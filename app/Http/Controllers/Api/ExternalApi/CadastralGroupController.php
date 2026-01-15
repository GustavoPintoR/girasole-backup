<?php

namespace App\Http\Controllers\Api\ExternalApi;

use App\Helpers\PlanHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CadastralGroupResource;
use App\Http\Resources\ForecastLogResource;
use App\Models\CadastralGroup;
use App\Models\ForecastLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Fields
 *
 * API for fields (read-only)
 *
 */
class CadastralGroupController extends Controller
{

    /**
     * Get all fields for the authenticated user.
     *
     * Returns a list of fields with their info, GeoJSON, and sensors.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @response 200 {
     *   "message": "Fields retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "user_id": 1,
     *       "name": "Field A",
     *       "description": "Main field",
     *       "total_area": 5000.5,
     *       "units_count": 2,
     *       "color": "#FF0000",
     *       "creation_method": "units",
     *       "original_geojson": {...},
     *       "boundary_geometry_json": {"type": "MultiPolygon", "coordinates": [...]},
     *       "centroid_json": {"type": "Point", "coordinates": [10.0, 20.0]},
     *       "sensors": [
     *         {
     *           "id": 1,
     *           "name": "Sensor 1",
     *           "type": "temperature",
     *           "status": "active"
     *         }
     *       ]
     *     }
     *   ]
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Payment required"
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $groups = CadastralGroup::with('sensors')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.cadastral_groups')]),
            'data' => CadastralGroupResource::collection($groups),
        ]);
    }

    /**
     * Get all fields for all users.
     * This endpoint only works if you have an integration role/token.
     * Returns a list of fields in the system with their info, GeoJSON, and sensors.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @response 200 {
     *   "message": "Fields retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "user_id": 1,
     *       "name": "Field A",
     *       "description": "Main field",
     *       "total_area": 5000.5,
     *       "units_count": 2,
     *       "color": "#FF0000",
     *       "creation_method": "units",
     *       "original_geojson": {...},
     *       "boundary_geometry_json": {"type": "MultiPolygon", "coordinates": [...]},
     *       "centroid_json": {"type": "Point", "coordinates": [10.0, 20.0]},
     *       "sensors": [
     *         {
     *           "id": 1,
     *           "name": "Sensor 1",
     *           "type": "temperature",
     *           "status": "active"
     *         }
     *       ]
     *     }
     *   ]
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Payment required"
     * }
     * @response 403 {
     *    "message": "Oops, seems you are not authorized to access this api."
     * }
     */
    public function allFields(Request $request): JsonResponse
    {
        $user = $request->user();

        if(!PlanHelper::checkIfTokenIsReadOnly($user)) {
            return response()->json([
                'message' => __('ui.token_not_read_only'),
            ], 403);
        }

        $groups = CadastralGroup::with('sensors')
            ->get();

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.cadastral_groups')]),
            'data' => CadastralGroupResource::collection($groups),
        ]);
    }

    /**
     * Get a specific field.
     *
     * Returns details of a field, including GeoJSON and sensors, if the user owns it.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @response 200 {
     *   "message": "Field retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "name": "Field A",
     *     "description": "Main field",
     *     "total_area": 5000.5,
     *     "units_count": 2,
     *     "color": "#FF0000",
     *     "creation_method": "units",
     *     "original_geojson": {...},
     *     "boundary_geometry_json": {"type": "MultiPolygon", "coordinates": [...]},
     *     "centroid_json": {"type": "Point", "coordinates": [10.0, 20.0]},
     *     "sensors": [
     *       {
     *         "id": 1,
     *         "name": "Sensor 1",
     *         "type": "temperature",
     *         "status": "active"
     *       }
     *     ],
     *     "recent_forecast_logs": [
     *        {
     *          "id": 189,
     *          "status": "success",
     *          "ran_at": "2025-11-26T14:32:10Z",
     *          "parameters": { "horizon": 14, "model": "v3" },
     *          "data": {
     *            "temperature": [ ... ],
     *            "precipitation": [ ... ]
     *          },
     *          "created_at": "2025-11-26T14:32:15Z"
     *        },
     *        {
     *          "id": 178,
     *          "status": "success",
     *          "ran_at": "2025-11-25T08:11:05Z",
     *          "parameters": { "horizon": 14 },
     *          "data": { ... },
     *          "created_at": "2025-11-25T08:11:10Z"
     *        }
     *      ]
     *   }
     * }
     * @response 403 {
     *   "message": "Unauthorized to view this field"
     * }
     * @response 404 {
     *   "message": "Field not found"
     * }
     * @response 422 {
     *   "message": "Validation failed",
     *   "errors": {
     *     "id": ["The id field is required."]
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $query = CadastralGroup::with([
            'sensors',
            'forecastLogs' => function ($q) {
                $q->whereNotNull('ran_at')
                ->latest('ran_at')
                ->limit(2);
            }
        ])->where('id', $id);

        if(!PlanHelper::checkIfTokenIsReadOnly($user)) { // that means its a regular user
            $query->where('user_id', $user->id);
        }

        $group = $query->first();

        if (!$group) {
            return response()->json([
                'message' => __('ui.model_unauthorized_not_found', ['model' => __('ui.cadastral_group')]),
            ], 403);
        }

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.cadastral_group')]),
            'data' => new CadastralGroupResource($group),
        ]);
    }

    /**
     * Get the forecast logs for a field.
     *
     * @authenticated
     * @urlParam field integer required The ID of the field
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @response 200 {
     *   "message": "Forecast logs retrieved successfully",
     *   "data": {
     *     "forecast_logs": [
     *      {
     *           "id": 189,
     *           "status": "success",
     *           "ran_at": "2025-11-26T14:32:10Z",
     *           "parameters": { "horizon": 14, "model": "v3" },
     *           "data": {
     *             "temperature": [ ... ],
     *             "precipitation": [ ... ]
     *           },
     *           "created_at": "2025-11-26T14:32:15Z"
     *       },
     *     ]
     *   }
     * }
     * @response 403 { "message": "Unauthorized to view this field" }
     * @response 404 { "message": "Field not found" }
     */
    public function forecastLogs(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $query = CadastralGroup::query();

        if(!PlanHelper::checkIfTokenIsReadOnly($user)) {
            $query->where('user_id', $user->id);
        }

        $field = $query->with(['forecastLogs'])->find($id);

        if (! $field) {
            return response()->json([
                'message' => $field ? __('ui.model_unauthorized_not_found', ['model' => __('ui.cadastral_group')])
                    : __('ui.model_not_found', ['model' => __('ui.cadastral_group')]),
            ], 404);
        }

        $logs = ForecastLog::where('field_id', $id)
            ->whereNotNull('ran_at')
            ->orderByDesc('ran_at')
            ->limit(2)->get();

        return response()->json([
            'message' => 'Last forecast logs retrieved successfully',
            'data' => [
                'forecast_logs' => ForecastLogResource::collection($logs),
            ],
        ]);
    }
}
