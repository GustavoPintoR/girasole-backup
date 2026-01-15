<?php

namespace App\Http\Controllers\Api\ExternalApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProvinceResource;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Provinces
 *
 * API for provinces
 *
 */
class ProvinceController extends Controller
{
    /**
     * Get all provinces.
     *
     * Returns a list of provinces, including region and cities.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @response 200 {
     *   "message": "Provinces retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Milan",
     *       "code": "MI",
     *       "region_id": 1,
     *       "region": {
     *         "id": 1,
     *         "name": "Lombardy",
     *         "code": "IT-LOM"
     *       },
     *       "cities": [
     *         {
     *           "id": 1,
     *           "name": "Milan",
     *           "cadastral_code": "MIL"
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
     *     "message": "Technicians are not allowed to access this endpoint."
     * }
     */
    public function index(Request $request): JsonResponse
    {

        $provinces = Province::with(['region', 'cities'])->get();

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.province')]),
            'data' => ProvinceResource::collection($provinces),
        ]);
    }

    /**
     * Get a specific province.
     *
     * Returns details of a province, including region and cities.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @bodyParam id integer required The ID of the province. Example: 1
     * @response 200 {
     *   "message": "Province retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Milan",
     *     "code": "MI",
     *     "region_id": 1,
     *     "region": {
     *       "id": 1,
     *       "name": "Lombardy",
     *       "code": "IT-LOM"
     *     },
     *     "cities": [
     *       {
     *         "id": 1,
     *         "name": "Milan",
     *         "cadastral_code": "MIL"
     *       }
     *     ]
     *   }
     * }
     * @response 404 {
     *   "message": "Province not found"
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
     * @response 403 {
     *   "message": "Payment required"
     * }
     * @response 403 {
     *     "message": "Technicians are not allowed to access this endpoint."
     * }
     */
    public function show(Request $request, Province $province): JsonResponse
    {
        $province->load(['region', 'cities']);

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.province')]),
            'data' => new ProvinceResource($province),
        ]);
    }
}
