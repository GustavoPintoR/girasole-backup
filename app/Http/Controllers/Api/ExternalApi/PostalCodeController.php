<?php

namespace App\Http\Controllers\Api\ExternalApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostalCodeResource;
use App\Models\PostalCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Postal Codes
 *
 * API for postal codes
 *
 */
class PostalCodeController extends Controller
{
    /**
     * Get all postal codes.
     *
     * Returns a list of postal codes, including cities.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @response 200 {
     *   "message": "Postal codes retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "code": "20121",
     *       "cities": [
     *         {
     *           "id": 1,
     *           "name": "Milan",
     *           "cadastral_code": "MIL",
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

        $postalCodes = PostalCode::with(['cities'])->get();

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.postal_codes')]),
            'data' => PostalCodeResource::collection($postalCodes),
        ]);
    }

    /**
     * Get a specific postal code.
     *
     * Returns details of a postal code, including cities.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @bodyParam id integer required The ID of the postal code. Example: 1
     * @response 200 {
     *   "message": "Postal code retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "code": "20121",
     *     "cities": [
     *       {
     *         "id": 1,
     *         "name": "Milan",
     *         "cadastral_code": "MIL",
     *         "pivot": {
     *           "zone": "Central",
     *           "notes": "Downtown area"
     *         }
     *       }
     *     ]
     *   }
     * }
     * @response 404 {
     *   "message": "Postal code not found"
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
    public function show(Request $request, PostalCode $postal_code): JsonResponse
    {
        $postal_code->load('cities');

        return response()->json([
            'message' => 'Postal code retrieved successfully',
            'data' => new PostalCodeResource($postal_code),
        ]);
    }
}
