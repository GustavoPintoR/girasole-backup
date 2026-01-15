<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\Sensor;
use App\Models\User;
use App\Services\InfluxDBService;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchSensors
{
    use AsAction;

    public string $commandSignature = 'girasole:fetch-sensors';

    public function __construct(protected InfluxDBService $influxdb)
    {
    }

    public function handle(string $range = '-7d')
    {
        $sensors = $this->influxdb->getSensors(range: $range);

        $fallbackOwnerId = auth()->id() ?? User::whereHas('roles', fn($q) => $q->where('name', UserRole::ADMINISTRATOR->value))->firstOrFail()->id;

        $toUpdate = [];
        $toCreate = [];

        $existingByName = Sensor::whereIn('name', array_column($sensors, 'sensor'))->pluck('id', 'name');

        $existingByUrn = Sensor::whereIn('urn', array_column($sensors, 'sensor'))->pluck('id', 'urn');

        foreach ($sensors as $sensor) {
            $payload = [
                'urn'       => $sensor['sensor'],
                'name'      => $sensor['sensor'],
                'firmware'  => $sensor['Mod'],
                'metadata'  => is_array($sensor['ops']) ? json_encode($sensor['ops']) : $sensor['ops'],
            ];

            if (isset($existingByName[$sensor['sensor']]) || isset($existingByUrn[$sensor['sensor']])) {
                $sensorId = $existingByName[$sensor['sensor']] ?? $existingByUrn[$sensor['sensor']];

                $currentOwner = Sensor::where('id', $sensorId)->value('owner_id');
                $payload['owner_id'] = $currentOwner;

                $toUpdate[] = array_merge(['id' => $sensorId], Arr::except($payload, ['name']));
            } else {
                $payload['owner_id'] = $fallbackOwnerId;
                $toCreate[] = $payload;
            }
        }

        foreach ($toUpdate as $data) {
            Sensor::where('id', $data['id'])->update(Arr::except($data, 'id'));
        }

        if ($toCreate) {
            Sensor::insert($toCreate);
        }
    }
}
