<?php

namespace App\Http\Controllers\Api\ExternalApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Regions
 *
 * API for regions
 *
 */
class RegionController extends Controller
{
    /**
     * Get all regions.
     *
     * Returns a list of regions, including provinces and cities.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @response 200 {
     *   "message": "Regions retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Lombardy",
     *       "code": "IT-LOM",
     *       "provinces": [
     *         {
     *           "id": 1,
     *           "name": "Milan",
     *           "code": "MI"
     *         }
     *       ],
     *       "cities": [
     *         {
     *           "id": 1,
     *           "name": "Milan",
     *           "code": "MIL"
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
        $regions = Region::with(['provinces', 'cities'])->get();

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.regions')]),
            'data' => RegionResource::collection($regions),
        ]);
    }

    /**
     * Get a specific region.
     *
     * Returns details of a region, including provinces and cities.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @bodyParam id integer required The ID of the region. Example: 1
     * @response 200 {
     *   "message": "Region retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Lombardy",
     *     "code": "IT-LOM",
     *     "provinces": [
     *       {
     *         "id": 1,
     *         "name": "Milan",
     *         "code": "MI"
     *       }
     *     ],
     *     "cities": [
     *       {
     *         "id": 1,
     *         "name": "Milan",
     *         "code": "MIL"
     *       }
     *     ]
     *   }
     * }
     * @response 404 {
     *   "message": "Region not found"
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
    public function show(Request $request, Region $region): JsonResponse
    {
        $region->load(['provinces', 'cities']);

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.region')]),
            'data' => new RegionResource($region),
        ]);
    }
}
