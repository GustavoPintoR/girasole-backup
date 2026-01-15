<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\CadastralUnit;
use App\Models\CadastralGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\AdjacencyCheckerService;
use App\Services\GeometryCalculatorService;
use App\Helpers\PlanHelper;
use App\Models\Company;

class CadastralGroupController extends Controller
{
    public function __construct(
        protected AdjacencyCheckerService $adjacencyChecker,
        protected GeometryCalculatorService $geometryCalculator,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => __('ui.missing_user_id'),
                'groups' => []
            ], 400);
        }

        $groups = CadastralGroup::where('user_id', $userId)
            ->select('id', 'name')
            ->get();

        return response()->json(['groups' => $groups]);
    }

    public function getGroupCoordinates(Request $request): JsonResponse
    {
        $group = CadastralGroup::find($request->group_id);
        $coordinates = $group->getCentroidCoordinates();

        return response()->json(['coordinates' => [
            'lng' => $coordinates['lng'],
            'lat' => $coordinates['lat']
        ]]);
    }

    /**
     * Check if units are adjacent
     */
    public function checkAdjacency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'required|integer|exists:cadastral_units,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $units = CadastralUnit::whereIn('id', $validated['unit_ids'])
            ->get();

        if ($units->count() !== count($validated['unit_ids'])) {
            return response()->json([
                'adjacent' => false,
                'message' => __('ui.some_units_not_found')
            ]);
        }

        $adjacent = $this->adjacencyChecker->areUnitsAdjacent($units);

        return response()->json([
            'adjacent' => $adjacent,
            'message' => $adjacent ? __('ui.units_are_adjacent') : __('ui.units_not_adjacent')
        ]);
    }

    /**
     * Calculate area for selected units
     */
    public function calculateArea(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'required|integer|exists:cadastral_units,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        try {
            $result = $this->geometryCalculator->calculateAreaForUnits($validated['unit_ids']);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'total_area' => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function mapData(int|string $userId): JsonResponse
    {
        $user = User::findOrFail((int) $userId);

        // Determine if company names should be shown
        $shouldShowCompany = false;
        if ($user->isSuperAdmin()) {
            $shouldShowCompany = Company::count() > 1;
        } else {
            $userCompanyCount = $user->companies()->count() + $user->companyOwner()->count();
            $shouldShowCompany = $userCompanyCount > 1;
        }

        $groups = CadastralGroup::query()
            ->visibleTo($user)
            ->with(['sensors' => function ($query) {
                $query->select('id', 'name', 'serial_number', 'description', 'latitude', 'longitude', 'cadastral_group_id', 'sensor_type_id', 'company_id')
                    ->with(['sensorType:id,name,description', 'company:id,owner_id']);
            }, 'company:id,name', 'events'])
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($group) use ($user, $shouldShowCompany) {
                $sensors = $group->sensors;
                $events = $group->events;

                if ($user->isTechnician()) {
                    $sensors = $sensors->filter(
                        fn ($sensor) => $sensor->company &&
                            $sensor->company->owner &&
                            PlanHelper::checkSubscription($sensor->company->owner)
                    )->values();
                }

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'total_area' => $group->total_area,
                    'units_count' => $group->units_count,
                    'creation_method' => $group->creation_method,
                    'color' => $group->color,
                    'boundary_geometry_json' => $group->boundary_geometry_json,
                    'centroid_json' => $group->centroid_json,
                    'sensors' => $sensors->map(function ($sensor) {
                        return [
                            'id' => $sensor->id,
                            'name' => $sensor->name,
                            'serial' => $sensor->serial_number,
                            'description' => $sensor->description,
                            'latitude' => (float) $sensor->latitude,
                            'longitude' => (float) $sensor->longitude,
                            'cadastral_group_id' => $sensor->cadastral_group_id,
                            'sensor_type' => $sensor->sensorType ? [
                                'id' => $sensor->sensorType->id,
                                'name' => $sensor->sensorType->name,
                                'description' => $sensor->sensorType->description,
                            ] : null,
                        ];
                    }),
                    'company' => ($shouldShowCompany && $group->company) ? [
                        'id' => $group->company->id,
                        'name' => $group->company->name,
                    ] : null,
                    'events' => $events->map(function ($event) {
                        return $event->toCalendarArray();
                    }),
                ];
            });

        return response()->json([
            'groups' => $groups,
            'shouldShowCompany' => $shouldShowCompany,
        ]);
    }
}
