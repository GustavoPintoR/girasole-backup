<?php

namespace App\Services;

use App\Models\SensorField;
use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use InfluxDB\Client;

class InfluxDBService
{
    /**
     * Fetch chart data from InfluxDB with flexible parameters.
     *
     * @param  string  $measurement  The measurement type (e.g., 'temperature')
     * @param  array  $locations  List of locations to query (e.g., ['indoor', 'outdoor'])
     * @param  string  $range  Time range for the query (e.g., '-7d', '-1h')
     * @param  string  $aggregationInterval  Aggregation interval (e.g., '1h', '30m')
     */
    public function getChartData(
        string $measurement = 'mqtt_consumer',
        array $locations = ['indoor', 'outdoor', 'office'],
        string $range = '-7d',
        string $aggregationInterval = '1h'
    ): array {
        $config = config('services.influxdb');
        $bucket = Arr::get($config, 'bucket');

        if (! $bucket) {
            Log::error('InfluxDB bucket configuration is missing.');

            return [
                'chartData' => [],
                'categories' => $locations,
            ];
        }

        $queryApi = app($bucket)->createQueryApi();

        $locationPattern = implode('|', array_map('preg_quote', $locations));

        // Build the Flux query dynamically
        $query = "
            from(bucket: \"$bucket\")
            |> range(start: $range)
            |> filter(fn: (r) => r._measurement == \"$measurement\")
            |> filter(fn: (r) => r.location =~ /^($locationPattern)$/)
            |> aggregateWindow(every: $aggregationInterval, fn: mean, createEmpty: false)
            |> pivot(rowKey: [\"_time\"], columnKey: [\"location\"], valueColumn: \"_value\")
            |> sort(columns: [\"_time\"], desc: false)
        ";

        try {
            $tables = $queryApi->queryStream($query);
            $chartData = [];

            foreach ($tables->each() as $record) {
                $values = $record->values;

                $utcTime = new DateTime($values['_time'], new \DateTimeZone('UTC'));
                $romeTimezone = new \DateTimeZone('Europe/Rome');
                $utcTime->setTimezone($romeTimezone);
                
                $formattedTime = $utcTime->format('M j, H:i');
                
                $dataPoint = ['time' => $formattedTime];
                foreach ($locations as $location) {
                    $dataPoint[$location] = isset($values[$location]) ? (float) $values[$location] : null;
                }

                $chartData[] = $dataPoint;
            }

            return [
                'chartData' => $chartData,
                'categories' => $locations,
            ];
        } catch (\Exception $e) {
            Log::error("InfluxDB query failed: {$e->getMessage()}");

            return [
                'chartData' => [],
                'categories' => $locations,
            ];
        }
    }

    /**
     * Fetch sensor data from InfluxDB and organize it hierarchically for dropdowns.
     *
     * @param string $measurement The measurement type (e.g., 'mqtt_consumer')
     * @param string $range Time range for the query (e.g., '-7d')
     * @param int $limit Maximum number of records per sensor
     * @return array Hierarchical data structure for sensors, ops, fields, and values
     */
    public function getSensors(string $measurement = 'mqtt_consumer', string $range = '-7d', int $limit = 50): array
    {
        $config = config('services.influxdb');
        $bucket = Arr::get($config, 'bucket');

        if (!$bucket) {
            Log::error('InfluxDB bucket configuration is missing.');
            return [];
        }

        $queryApi = app($bucket)->createQueryApi();

        $fields = Arr::get($config, 'sensors_fields');
        $queryFields = array_unique(array_merge(['Sens'], $fields));
        $queryFieldsString = '"' . implode('", "', $queryFields) . '"';

        $query = "
            from(bucket: \"$bucket\")
              |> range(start: $range)
              |> filter(fn: (r) => r._measurement == \"$measurement\")
              |> map(fn: (r) => ({ r with _value: float(v: r._value) }))
              |> keep(columns: [$queryFieldsString])
              |> limit(n: $limit)
        ";

        try {
            $tables = $queryApi->queryStream($query);
            $sensors = [];

            foreach ($tables->each() as $record) {
                $values = $record->values;
                $sens = $values['Sens'];
                $mod = $values['Mod'];
                $op = $values['Op'];
                $field = $values['_field'];
                $value = (float) $values['_value'];

                if (!isset($sensors[$sens])) {
                    $sensors[$sens] = [
                        'sensor' => $sens,
                        'Mod' => $mod,
                        'ops' => [],
                    ];
                }

                // Initialize operation if not exists
                if (!isset($sensors[$sens]['ops'][$op])) {
                    $sensors[$sens]['ops'][$op] = [
                        'op' => $op,
                        'fields' => [],
                    ];
                }

                // Initialize field if not exists
                if (!isset($sensors[$sens]['ops'][$op]['fields'][$field])) {
                    $sensors[$sens]['ops'][$op]['fields'][$field] = [
                        'field' => $field,
                        'values' => [],
                    ];
                }

                // Add value if not already present
                if (!in_array($value, $sensors[$sens]['ops'][$op]['fields'][$field]['values'])) {
                    $sensors[$sens]['ops'][$op]['fields'][$field]['values'][] = $value;
                }
            }

            // Convert to indexed array and sort values
            $result = array_values(array_map(function ($sensorData) {
                $sensorData['ops'] = array_values(array_map(function ($opData) {
                    $opData['fields'] = array_values(array_map(function ($fieldData) {
                        sort($fieldData['values']); // Sort values for consistency
                        return $fieldData;
                    }, $opData['fields']));
                    return $opData;
                }, $sensorData['ops']));
                return $sensorData;
            }, $sensors));

            return $result;
        } catch (\Exception $e) {
            Log::error("InfluxDB query failed: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch chart data from InfluxDB based on sensor filters.
     *
     * @param string $sens The sensor ID
     * @param string $op The operation
     * @param string $time The time range (e.g., '-7d')
     * @param string $field The field name
     * @param string $mod The firmware
     * @param string $measurement The measurement type
     * @param string $aggregationInterval The aggregation interval (e.g., '1h')
     * @return array Chart data and categories
     */
    public function getChartDataByFilters(
        string $sens,
        string $op,
        string $time,
        string $field,
        string $mod,
        string $measurement = 'mqtt_consumer',
        string $aggregationInterval = '1h'
    ): array {
        $config = config('services.influxdb');
        $bucket = Arr::get($config, 'bucket');

        if (!$bucket || !$sens || !$op || !$field) {
            Log::error('InfluxDB configuration or filters missing.');
            return [
                'chartData' => [],
                'categories' => ['value'],
            ];
        }

        if (!$mod) {
            Log::error("No Mod found for sensor: $sens");
            return [
                'chartData' => [],
                'categories' => ['value'],
            ];
        }

        $queryApi = app($bucket)->createQueryApi();

        $time = $time."d";
        //              |> filter(fn: (r) => r[\"Mod\"] == \"$mod\")

        $query = "
            from(bucket: \"$bucket\")
              |> range(start: $time)
              |> filter(fn: (r) => r._measurement == \"$measurement\")
              |> filter(fn: (r) => r[\"Sens\"] == \"$sens\")
              |> filter(fn: (r) => r[\"Op\"] == \"$op\")
              |> filter(fn: (r) => r[\"_field\"] == \"$field\")
              |> aggregateWindow(every: $aggregationInterval, fn: mean, createEmpty: true)
              |> sort(columns: [\"_time\"], desc: false)
        ";

        $category = SensorField::where('label', $field)->first()?->name;

        try {
            $tables = $queryApi->queryStream($query);
            $chartData = [];

            foreach ($tables->each() as $record) {
                $values = $record->values;

                // if (!isset($values['_time']) || !isset($values['_value'])) {
                //     continue;
                // }

                $utcTime = new DateTime($values['_time'], new \DateTimeZone('UTC'));
                $romeTimezone = new \DateTimeZone('Europe/Rome');
                $utcTime->setTimezone($romeTimezone);
                
                $formattedTime = $utcTime->format('M j, H:i');

                if (!isset($values['_time']) || !isset($values['_value'])) {
                    $chartData[] = [
                        'time' => $formattedTime,
                    ];
                }else{
                    $chartData[] = [
                        'time' => $formattedTime,
                        $category => (float) $values['_value']
                    ];
                }
                
            }

            return [
                'chartData' => $chartData,
                'categories' => [$category],
            ];
        } catch (\Exception $e) {
            Log::error("InfluxDB query failed: {$e->getMessage()}");
            return [
                'chartData' => [],
                'categories' => ['value'],
            ];
        }
    }

    /**
     * Fetch distinct operations for a sensor and time range.
     *
     * @param string $sens The sensor ID
     * @param string $time The time range (e.g., '-7d')
     * @param string $measurement The measurement type
     * @return array Distinct operation values
     */
    public function getDistinctOps(string $sens, string $time, string $measurement = 'mqtt_consumer'): array
    {
        $config = config('services.influxdb');
        $bucket = Arr::get($config, 'bucket');

        if (!$bucket || !$sens) {
            Log::error('InfluxDB configuration or sensor ID missing.');
            return [];
        }

        $queryApi = app($bucket)->createQueryApi();

        $query = "
            from(bucket: \"$bucket\")
              |> range(start: $time)
              |> filter(fn: (r) => r._measurement == \"$measurement\")
              |> filter(fn: (r) => r[\"Sens\"] == \"$sens\")
              |> keep(columns: [\"Op\"])
              |> distinct(column: \"Op\")
        ";

        try {
            $tables = $queryApi->queryStream($query);
            $ops = [];

            foreach ($tables->each() as $record) {
                $values = $record->values;
                if (isset($values['Op'])) {
                    $ops[] = $values['Op'];
                }
            }

            return array_unique($ops);
        } catch (\Exception $e) {
            Log::error("Failed to fetch operations for sensor $sens: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch distinct fields for a sensor, operation, and time range.
     *
     * @param string $sens The sensor ID
     * @param string $op The operation
     * @param string $time The time range (e.g., '-7d')
     * @param string $measurement The measurement type
     * @return array Distinct field values
     */
    public function getDistinctFields(string $sens, string $op, string $time, string $measurement = 'mqtt_consumer'): array
    {
        $config = config('services.influxdb');
        $bucket = Arr::get($config, 'bucket');

        if (!$bucket || !$sens || !$op) {
            Log::error('InfluxDB configuration, sensor ID, or operation missing.');
            return [];
        }

        $queryApi = app($bucket)->createQueryApi();

        $query = "
            from(bucket: \"$bucket\")
              |> range(start: $time)
              |> filter(fn: (r) => r._measurement == \"$measurement\")
              |> filter(fn: (r) => r[\"Sens\"] == \"$sens\")
              |> filter(fn: (r) => r[\"Op\"] == \"$op\")
              |> keep(columns: [\"_field\"])
              |> distinct(column: \"_field\")
        ";

        try {
            $tables = $queryApi->queryStream($query);
            $fields = [];

            foreach ($tables->each() as $record) {
                $values = $record->values;
                if (isset($values['_field'])) {
                    $fields[] = $values['_field'];
                }
            }

            return array_unique($fields);
        } catch (\Exception $e) {
            Log::error("Failed to fetch fields for sensor $sens and op $op: {$e->getMessage()}");
            return [];
        }
    }
}
