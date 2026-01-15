<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sensor;
use App\Models\SensorField;
use App\Services\InfluxDBService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SensorController extends Controller
{

    /**
     * @param InfluxDBService $influxDBService
     */
    public function __construct(protected InfluxDBService $influxDBService)
    {}

    /**
     * Fetch distinct operations for a sensor and time range.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getOps(Request $request): JsonResponse
    {
        $time = $request->input('time', '');
        $sens = $request->input('sens', '');

        if (!$time || !$sens) {
            return response()->json(['ops' => []]);
        }

        $sensor = Sensor::with(['sensorType.sensorOperations'])->where('name', $sens)->first();

        if (!$sensor) {
            return response()->json(['ops' => []]);
        }

        $ops = $this->influxDBService->getDistinctOps($sensor->urn, $time);

        if (empty($ops)) {
            return response()->json(['ops' => []]);
        }

        $sensorOps = $sensor?->sensorType?->sensorOperations
            ->whereIn('label', $ops)
            ->map(fn($op) => [
                'name' => $op->name,
                'label' => $op->label,
            ])
            ->toArray();

        return response()->json(['ops' => array_values($sensorOps)]);
    }

    /**
     * Fetch distinct fields for a sensor, operation, and time range.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFields(Request $request): JsonResponse
    {
        $time = $request->input('time', '');
        $sens = $request->input('sens', '');
        $op = $request->input('op', '');

        if (!$time || !$sens || !$op) {
            return response()->json(['fields' => []]);
        }

        $sensor = Sensor::where('name', $sens)->first();
        $fields = $this->influxDBService->getDistinctFields($sensor->urn, $op, $time);

        if (empty($fields)) {
            return response()->json(['fields' => []]);
        }

        $sensorFields = SensorField::whereIn('label', $fields)
            ->select('name', 'label')
            ->orderBy('name')
            ->get()
            ->toArray();

        return response()->json(['fields' => $sensorFields]);
    }
}
