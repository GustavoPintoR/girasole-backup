<?php

namespace App\Http\Controllers\Api\ExternalApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Cities
 *
 * API for cities
 *
 */
class CityController extends Controller
{
    /**
     * Get all cities.
     *
     * Returns a list of cities, including province, region, and postal codes.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @response 200 {
     *   "message": "Cities retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Milan",
     *       "cadastral_code": "MIL",
     *       "province_id": 1,
     *       "region_id": 1,
     *       "province": {
     *         "id": 1,
     *         "name": "Milan",
     *         "code": "MI"
     *       },
     *       "region": {
     *         "id": 1,
     *         "name": "Lombardy",
     *         "code": "IT-LOM"
     *       },
     *       "postalCodes": [
     *         {
     *           "id": 1,
     *           "code": "20121",
     *           "pivot": {
     *             "zone": "Central",
     *             "notes": "Downtown area"
     *           }
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
        $cities = City::with(['province', 'region', 'postalCodes'])->get();

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.cities')]),
            'data' => CityResource::collection($cities),
        ]);
    }

    /**
     * Get a specific city.
     *
     * Returns details of a city, optionally including province, region, and postal codes.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @bodyParam id integer required The ID of the city. Example: 1
     * @queryParam include string Comma-separated list of relationships to include (province, region, postalCodes). Example: province,region,postalCodes
     * @response 200 {
     *   "message": "City retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Milan",
     *     "cadastral_code": "MIL",
     *     "province_id": 1,
     *     "region_id": 1,
     *     "province": {
     *       "id": 1,
     *       "name": "Milan",
     *       "code": "MI"
     *     },
     *     "region": {
     *       "id": 1,
     *       "name": "Lombardy",
     *       "code": "IT-LOM"
     *     },
     *     "postalCodes": [
     *       {
     *         "id": 1,
     *         "code": "20121",
     *         "pivot": {
     *           "zone": "Central",
     *           "notes": "Downtown area"
     *         }
     *       }
     *     ]
     *   }
     * }
     * @response 404 {
     *   "message": "City not found"
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
    public function show(Request $request, City $city): JsonResponse
    {
        $city = $city->load(['province', 'region', 'postalCodes']);

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.city')]),
            'data' => new CityResource($city),
        ]);
    }
}
