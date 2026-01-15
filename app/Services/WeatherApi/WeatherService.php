<?php

namespace App\Services\WeatherApi;

use App\Enums\ForecastStatus;
use App\Http\Integrations\WeatherApi\Requests\ForecastRequest;
use App\Http\Integrations\WeatherApi\WeatherApiConnector;
use App\Models\ForecastSetup;
use App\Models\ForecastLog;
use Illuminate\Support\Facades\Log;
use Saloon\Exceptions\InvalidPoolItemException;
use Saloon\Http\Pool;
use Carbon\Carbon;

class WeatherService
{
    protected bool $batchMode;

    public function __construct()
    {
        $this->batchMode = config('girasole.weather_forecast.batch_mode', false);
    }

    /**
     * @throws InvalidPoolItemException
     */
    public function runBatch(): void
    {
        $romeToday = Carbon::now('Europe/Rome');
        $todayDay = strtolower($romeToday->englishDayOfWeek);

        $hasCompleted = ForecastLog::whereDate('ran_at', $romeToday->format('Y-m-d'))
            ->whereIn('status', ['success', 'failed'])
            ->exists();

        if ($hasCompleted) {
            Log::channel('weather_api')->info('Forecast batch already completed today.');
            return;
        }

        $setups = ForecastSetup::with('field')
            ->where($todayDay, true)
            ->whereHas('field', fn($q) => $q->whereNotNull('centroid'))
            ->get();

        if ($setups->isEmpty()) {
            return;
        }

        $maxFields = floor(250 / 2);
        if ($setups->count() > $maxFields && !$this->batchMode) {
            Log::channel('weather_api')->warning('Truncating fields to respect 250 calls/day limit.', [
                'total' => $setups->count(), 'running' => $maxFields,
            ]);
            $setups = $setups->take($maxFields);
        }

        foreach ($setups as $setup) {
            $params1 = implode(',', $this->paramGroup1());
            $params2 = implode(',', $this->paramGroup2());

            ForecastLog::updateOrCreate(
                ['field_id' => $setup->field_id, 'ran_at' => $romeToday, 'parameters' => $params1],
                ['status' => ForecastStatus::PENDING->value, 'data' => null]
            );
            ForecastLog::updateOrCreate(
                ['field_id' => $setup->field_id, 'ran_at' => $romeToday, 'parameters' => $params2],
                ['status' => ForecastStatus::PENDING->value, 'data' => null]
            );
        }

        $connector = new WeatherApiConnector();

        if ($this->batchMode) {
            $this->runInBatchMode($connector, $setups, $romeToday);
        } else {
            $this->runOneByOne($connector, $setups, $romeToday);
        }
    }

    /**
     * @throws InvalidPoolItemException
     */
    private function runOneByOne(WeatherApiConnector $connector, $setups, Carbon $ranAt): void
    {
        foreach ($setups as $setup) {
            $lat = $setup->field->getCentroidLatitude();
            $lon = $setup->field->getCentroidLongitude();

            if (!$lat || !$lon) continue;

            $coord = "$lat,$lon";

            $this->fetchAndSave($connector, $coord, $this->paramGroup1(),$setup->field_id, $ranAt);
//            usleep(config('girasole.weather_forecast.delay_ms', 500) * 1000);

            $this->fetchAndSave($connector, $coord, $this->paramGroup2(), $setup->field_id, $ranAt);
//            usleep(config('girasole.weather_forecast.delay_ms', 500) * 1000);
        }

        Log::channel('weather_api')->info('All forecasts completed (sequential with sleep).');
    }

    private function fetchAndSave($connector, string $coord, array $params, int $fieldId, Carbon $ranAt): void
    {
        $paramString = implode(',', $params);

        try {
            $response = $connector->send(new ForecastRequest($coord, $params));


            $log = ForecastLog::where('field_id', $fieldId)
                ->where('ran_at', $ranAt)
                ->where('parameters', $paramString)
                ->first();

            if (!$log) {
                Log::channel('weather_api')->error('Log not found after request!', compact('fieldId', 'paramString'));
                return;
            }

            if ($response->successful()) {
                $log->update([
                    'status' => ForecastStatus::SUCCESS->value,
                    'data'   => $response->json(),
                ]);
                Log::channel('weather_api')->info('Success', ['field_id' => $fieldId]);
            } else {
                $log->update([
                    'status' => ForecastStatus::FAILED->value,
                    'data'   => ['error' => $response->body(), 'code' => $response->status()]
                ]);
                Log::channel('weather_api')->error('Failed', ['field_id' => $fieldId, 'status' => $response->status()]);
            }
        } catch (\Throwable $e) {
            Log::channel('weather_api')->error('Exception', [
                'field_id' => $fieldId,
                'error' => $e->getMessage()
            ]);

            ForecastLog::where('field_id', $fieldId)
                ->where('ran_at', $ranAt)
                ->where('parameters', $paramString)
                ->update(['status' => ForecastStatus::FAILED->value, 'data' => ['error' => $e->getMessage()]]);
        }
    }

    private function runInBatchMode(WeatherApiConnector $connector, $setups, Carbon $ranAt): void
    {
        $coordinates = $setups->map(function ($setup) {
            $lat = $setup->field->getCentroidLatitude();
            $lon = $setup->field->getCentroidLongitude();
            return $lat && $lon ? "$lat,$lon" : null;
        })->filter()->implode('+');

        if ($coordinates === '') {
            Log::channel('weather_api')->info('No valid coordinates for batch mode.');
            return;
        }

        $fieldIds = $setups->pluck('field_id')->sort()->values()->toArray();

        $paramGroup1 = $this->paramGroup1();
        $paramGroup2 = $this->paramGroup2();
        $paramString1 = implode(',', $paramGroup1);
        $paramString2 = implode(',', $paramGroup2);

        $delayMs = config('girasole.weather_forecast.delay_ms', 500) * 1000;

        Log::channel('weather_api')->info('Starting batch mode forecast', [
            'total_fields' => $setups->count(),
            'field_ids'    => $fieldIds,
        ]);

        $this->fetchAndSaveBatch($connector, $coordinates, $paramGroup1, $paramString1, $setups, $ranAt, $fieldIds);
        usleep($delayMs);

        $this->fetchAndSaveBatch($connector, $coordinates, $paramGroup2, $paramString2, $setups, $ranAt, $fieldIds);

        Log::channel('weather_api')->info('Batch mode completed successfully', [
            'total_fields' => $setups->count(),
            'field_ids'    => $fieldIds,
        ]);
    }

    private function fetchAndSaveBatch(WeatherApiConnector $connector, string $coordinates, array $params, string $paramString, $setups, Carbon $ranAt, array $fieldIds): void {
        try {
            $response = $connector->send(new ForecastRequest($coordinates, $params));

            if (!$response->successful()) {
                Log::channel('weather_api')->error('Batch forecast request FAILED', [
                    'parameters'        => $paramString,
                    'status_code'       => $response->status(),
                    'response_preview'  => substr($response->body(), 0, 1000),
                    'affected_field_ids'=> $fieldIds,
                ]);

                ForecastLog::where('ran_at', $ranAt)
                    ->where('parameters', $paramString)
                    ->update([
                        'status' => ForecastStatus::FAILED->value,
                        'data'   => ['error' => $response->body(), 'code' => $response->status()],
                    ]);
                return;
            }

            $json = $response->json();
            $matchedFieldIds = [];

            foreach ($json['data'][0]['coordinates'] ?? [] as $coordData) {
                $lat = $coordData['lat'] ?? null;
                $lon = $coordData['lon'] ?? null;
                if (is_null($lat) || is_null($lon)) continue;

                $setup = $setups->first(fn($s) =>
                    abs($s->field->getCentroidLatitude() - $lat) < 0.001 &&
                    abs($s->field->getCentroidLongitude() - $lon) < 0.001
                );

                if (!$setup) continue;

                $log = ForecastLog::where('field_id', $setup->field_id)
                    ->where('ran_at', $ranAt)
                    ->where('parameters', $paramString)
                    ->first();

                if (!$log) {
                    Log::channel('weather_api')->warning('ForecastLog missing during batch save', [
                        'field_id' => $setup->field_id,
                        'parameters' => $paramString,
                    ]);
                    continue;
                }

                $coordSpecificData = [
                    'location' => ['lat' => $lat, 'lon' => $lon],
                    'dates'    => $coordData['dates'] ?? [],
                ];

                $log->update([
                    'status' => ForecastStatus::SUCCESS->value,
                    'data'   => $coordSpecificData,
                ]);

                $matchedFieldIds[] = $setup->field_id;
            }

            Log::channel('weather_api')->info('Batch forecast group SUCCESS', [
                'parameters'        => $paramString,
                'fields_requested'  => count($fieldIds),
                'fields_matched'    => count($matchedFieldIds),
                'matched_field_ids' => $matchedFieldIds,
                'missing_field_ids' => array_diff($fieldIds, $matchedFieldIds),
            ]);

        } catch (\Throwable $e) {
            Log::channel('weather_api')->error('Exception in batch forecast request', [
                'parameters'        => $paramString,
                'error'             => $e->getMessage(),
                'affected_field_ids'=> $fieldIds,
            ]);

            ForecastLog::where('ran_at', $ranAt)
                ->where('parameters', $paramString)
                ->update([
                    'status' => ForecastStatus::FAILED->value,
                    'data'   => ['error' => $e->getMessage()],
                ]);
        }
    }

    private function paramGroup1(): array
    {
        return [
            't_mean_2m_1h:C',
            'relative_humidity_mean_2m_1h:p',
            'vapor_pressure_deficit_mean_2m_1h:hPa',
            'leaf_wetness:idx',
            'precip_1h:mm',
            'prob_precip_1h:p',
            'prob_precip_24h:p',
            'wind_speed_2m:kmh',
            'global_rad:W',
            'dew_point_2m:C',
        ];
    }

    private function paramGroup2(): array
    {
        return [
            'evapotranspiration_1h:mm',
            'soil_moisture_index_-15cm:idx',
            'soil_moisture_index_-50cm:idx',
            'volumetric_soil_water_-15cm:m3m3',
            'volumetric_soil_water_-50cm:m3m3',
            'soil_moisture_deficit:mm',
            't_-15cm:C',
            't_-50cm:C',
            'precip_24h:mm',
            'growing_degree_days_accumulated:gdd',
        ];
    }

    /**
     * Retrieves the configured limit for weather forecast calls for the current day.
     *
     * @return int The limit of weather forecast calls allowed on the current date.
     */
    private function limitForToday(): int
    {
        $day = strtolower(Carbon::today()->englishDayOfWeek);
        return config("girasole.weather_forecast.calls.{$day}", 250);
    }
}
