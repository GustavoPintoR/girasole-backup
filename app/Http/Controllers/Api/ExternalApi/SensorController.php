<?php

namespace App\Http\Controllers\Api\ExternalApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\SensorResource;
use App\Models\Sensor;
use App\Services\InfluxDBService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Sensors
 *
 * API for sensor
 *
 */
class SensorController extends Controller
{

    public function __construct(protected InfluxDBService $influxDBService) {}

    /**
     * Display a paginated list of sensors with their type, operations, and cadastral group.
     *
     * This endpoint retrieves a list of sensors, optionally filtered by type, for the authenticated user
     * or users with read-only permissions. Includes sensor type, operations, cadastral group, and owner details.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @queryParam type string Optional. Filter sensors by type. Example: temperature
     * @queryParam page int Optional. Page number for pagination. Example: 1
     * @queryParam per_page int Optional. Number of items per page (default: 10). Example: 10
     *
     * @response 200 {
     *   "message": "Sensors retrieved successfully",
     *   "data": [
     *       {
     *         "id": 1,
     *         "name": "Temperature Sensor",
     *         "type": "temperature",
     *         "serial_number": "SN123456",
     *         "urn": "urn:sensor:123",
     *         "iccid": "1234567890123456789",
     *         "transmission_module_identification": "TM123",
     *         "description": "A temperature sensor for environmental monitoring",
     *         "latitude": "40.71280000",
     *         "longitude": "-74.00600000",
     *         "firmware": "v1.0.0",
     *         "metadata": {"calibration": "2025-01-01"},
     *         "sensor_type": {
     *           "id": 1,
     *           "name": "Temperature",
     *           "description": "Measures ambient temperature",
     *           "operations": [
     *             {
     *               "id": 1,
     *               "name": "Read Temperature",
     *               "description": "Reads current temperature in Celsius"
     *             }
     *           ]
     *         },
     *         "fields": {
     *           "id": 1,
     *           "name": "Downtown Monitoring"
     *         },
     *         "owner": {
     *           "id": 1,
     *           "name": "John Doe",
     *           "email": "john@example.com"
     *         },
     *         "created_at": "2025-10-03T09:33:00Z"
     *       }
     *     ],
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 403 {
     *    "message": "Technicians are not allowed to access this endpoint."
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $sensors = Sensor::type($request->query('type'))
            ->ownedBy($request->user()->id)
            ->with(['sensorType.sensorOperations', 'cadastralGroup', 'owner'])
            ->get();

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.sensors')]),
            'data' => SensorResource::collection($sensors),
        ]);
    }

    /**
     * Display a sensor with its type, operations, and cadastral group.
     *
     * This endpoint retrieves details of a specific sensor, including its sensor type,
     * associated operations, and cadastral group. Only accessible with read-only permissions
     * or by the sensor's owner.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @param Request $request
     * @param Sensor $sensor
     * @return JsonResponse
     *
     * @response 200 {
     *   "message": "Sensor retrieved successfully",
     *   "sensor": {
     *     "id": 1,
     *     "name": "Temperature Sensor",
     *     "type": "temperature",
     *     "serial_number": "SN123456",
     *     "urn": "urn:sensor:123",
     *     "iccid": "1234567890123456789",
     *     "transmission_module_identification": "TM123",
     *     "description": "A temperature sensor for environmental monitoring",
     *     "latitude": "40.71280000",
     *     "longitude": "-74.00600000",
     *     "firmware": "v1.0.0",
     *     "metadata": {"calibration": "2025-01-01"},
     *     "sensor_type": {
     *       "id": 1,
     *       "name": "Temperature",
     *       "description": "Measures ambient temperature",
     *       "operations": [
     *         {
     *           "id": 1,
     *           "name": "Read Temperature",
     *           "description": "Reads current temperature in Celsius"
     *         }
     *       ]
     *     },
     *     "fields": {
     *       "id": 1,
     *       "name": "Downtown Monitoring"
     *     },
     *     "owner": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     },
     *     "created_at": "2025-10-03T09:33:00Z"
     *   }
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "message": "Sensor not found"
     * }
     * @response 403 {
     *     "message": "Technicians are not allowed to access this endpoint."
     * }
     */
    public function show(Request $request, Sensor $sensor): JsonResponse
    {
       $sensor->load(['sensorType.sensorOperations', 'cadastralGroup', 'owner']);

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.sensor')]),
            'sensor' => new SensorResource($sensor),
        ]);
    }

    /**
     * Get a sensor by ID with data from InfluxDB.
     *
     * Returns sensor data with the most recent measurements for the specified field from influxdb.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @bodyParam sensor_id integer required The ID of the sensor. Example: 1
     * @bodyParam time_range string required Time range to query influx db. Example: 30
     * @bodyParam sensor_operation string required The sensor operation. Example: T01
     * @bodyParam field string The sensor field. Example: CH
     * @response 200 {
     *   "message": "Sensor retrieved successfully",
     *   "data": {
     *     "sensor": {
     *       "id": 1,
     *       "serial_number": "123",
     *       "urn:"1004828F2",
     *       "sensor_type": {
     *           "name": "Malisimo"
     *       },
     *       "fields": {
     *         "id": 1,
     *         "name": "Field A"
     *       },
     *       "longitude": 10.0,
     *       "latitude": 20.0,
     *       "description": "Main sensor",
     *       "firmware": "200"
     *     },
     *      "measurements": []
     *   }
     * }
     * @response 403 {
     *   "message": "Unauthorized to view this sensor"
     * }
     * @response 404 {
     *   "message": "Sensor not found"
     * }
     * @response 422 {
     *   "message": "Validation failed",
     *   "errors": {
     *     "sensor_id": ["The sensor_id field is required."]
     *   }
     * }
     * @response 422 {
     *   "message": "URN not found"
     * }
     * @response 422 {
     *   "message": "Failed to fetch measurement"
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *     "message": "Technicians are not allowed to access this endpoint."
     * }
     */
    public function getInfluxData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sensor_id' => ['required', 'integer', 'exists:sensors,id'],
            'time_range' => ['required', 'integer'],
            'sensor_operation' => ['required', 'string'],
            'field' => ['string', 'nullable'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('ui.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $sensor = Sensor::with(['sensorType', 'cadastralGroup'])
            ->where('owner_id', $user->id)
            ->where('id', $request->sensor_id)
            ->first();

        if (!$sensor) {
            return response()->json([
                'message' => __('ui.model_unauthorized_not_found', ['model' => __('ui.sensor')]),
            ], 403);
        }

        if (!$sensor->urn) {
            return response()->json([
                'message' => __('ui.urn_not_found'),
            ], 422);
        }

        $field = $request->field;
        $data = null;

        try {
            $data = $this->influxDBService->getChartDataByFilters($sensor->urn, $request->sensor_operation, (int) $request->time_range, $field, $sensor->firmware);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('ui.failed_to_measure'),
            ], 422);
        }

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.sensor')]),
            'data' => [
                'measurements' => $data,
                'sensor' => new SensorResource($sensor),
            ],
        ]);
    }
}

