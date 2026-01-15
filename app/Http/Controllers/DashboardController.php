<?php

namespace App\Http\Controllers;

use App\Models\CadastralGroup;
use App\Models\Sensor;
use App\Models\SensorField;
use App\Services\InfluxDBService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Helpers\PlanHelper;

class DashboardController extends Controller
{
    public function __construct(protected InfluxDBService $influxDBService) {}

    // public function dashboard()
    // {
    //     return Inertia::render('Dashboard');
    // }

    /**
     * @throws \Exception
     */
    public function dashboard2(Request $request): Response|RedirectResponse
    {
        $time = $request->input('time', '');
        $sens = $request->input('sens', '');
        $op = $request->input('op', '');
        $field = $request->input('field', '');

        // Get metrics for logged-in user
        $user = Auth::user();
        $cadastralGroupsCount = CadastralGroup::query()->visibleTo($user)->count();
        $sensorsQuery = Sensor::query()->visibleTo($user);

        $sensors = $sensorsQuery
            ->with(['sensorType', 'cadastralGroup', 'company.owner', 'sensorType.sensorOperations'])
            ->whereHas('sensorType')
            ->get();

        if ($user->isTechnician()) {
            $sensors = $sensors->filter(
                fn ($sensor) => $sensor->company &&
                    $sensor->company->owner &&
                    PlanHelper::checkSubscription($sensor->company->owner)
            )->values();
        }
        $sensorsCount = $sensors->count();

        $sensorModels = $sensors;
        $sensors = $sensors->map(fn ($sensor) => [
            'id' => $sensor->id,
            'type' => $sensor->sensorType?->name ?? 'Unknown',
            'serial' => $sensor->urn,
            'sensor' => $sensor->name,
            'cadastral_group' => $sensor->cadastralGroup,
            'longitude' => $sensor->longitude,
            'latitude' => $sensor->latitude,
            'description' => $sensor->description ?? '',
            'Mod' => $sensor->firmware,
        ])->toArray();

        // select first sensor if exists and no params
        $isInitialLoad = ! $time && ! $sens && ! $op && ! $field;
        if ($isInitialLoad && $sensorsCount > 0) {
            $sortedSensors = collect($sensors)->sortBy('sensor')->values()->toArray();
            $time = '-7d';

            // find first sensor with ops
            foreach ($sortedSensors as $sensorData) {
                $selectedSensor = $sensorModels->firstWhere('name', $sensorData['sensor']);

                if (! $selectedSensor || ! $selectedSensor->urn) {
                    continue;
                }

                $ops = $this->influxDBService->getDistinctOps($selectedSensor->urn, $time);

                if (empty($ops)) {
                    continue;
                }

                $sensorOps = $selectedSensor->sensorType?->sensorOperations
                    ->whereIn('label', $ops)
                    ->sortBy('name')
                    ->values();

                if ($sensorOps->isEmpty()) {
                    continue;
                }

                // set and continue with fields
                $sens = $sensorData['sensor'];
                $op = $sensorOps->first()->label;

                $fields = $this->influxDBService->getDistinctFields($selectedSensor->urn, $op, $time);

                if (! empty($fields)) {
                    // Get first field
                    $sensorFields = SensorField::whereIn('label', $fields)
                        ->orderBy('name')
                        ->get();

                    if ($sensorFields->isNotEmpty()) {
                        $field = $sensorFields->first()->label;
                    }
                }
                break;
            }
        }

        // Initialize default data and errors
        $data = ['chartData' => [], 'categories' => ['value']];
        $selectedField = 'value';
        $errors = [];

        // Fetch chart data if all required inputs are provided
        if ($time && $sens && $op && $field) {
            $sensor = Sensor::where('name', $sens)->first();

            if (!$sensor) {
                $errors['sensor'] = __('ui.sensor_not_found');
            } else if(!$sensor->urn) {
                $errors['sensor'] = __('ui.urn_not_found');
            }else {
                try {
                    $data = $this->influxDBService->getChartDataByFilters($sensor->urn, $op, (int) $time, $field, $sensor->firmware);
                } catch (\Exception $e) {
                    $errors['chart'] = 'Failed to fetch chart data';
                }

                $sensorField = SensorField::where('label', $field)->first();
                $selectedField = $sensorField ? $sensorField->label : $field;
            }
        }

        return Inertia::render('Dashboard2', [
            'chartData' => $data['chartData'],
            'categories' => $data['categories'],
            'sensors' => $sensors,
            'field' => $selectedField,
            'selectedTime' => $time,
            'selectedSens' => $sens,
            'selectedOp' => $op,
            'selectedField' => $field,
            'metrics' => [
                'cadastralGroups' => $cadastralGroupsCount,
                'sensors' => $sensorsCount,
            ],
            'errors' => $errors,
            'showSensors' => config('girasole.dashboard.show_sensors') && $sensorsCount > 0,
            'showWeather' => config('girasole.dashboard.show_weather') && $sensorsCount > 0,
        ]);
    }
}
