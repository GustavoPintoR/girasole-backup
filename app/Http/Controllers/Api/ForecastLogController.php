<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ForecastLogService;
use Illuminate\Http\Request;

class ForecastLogController extends Controller
{
    public function __construct(
        protected ForecastLogService $forecastLogService,
    ) {}

    public function getData(Request $request)
    {
        $cadastralGroupId = $request->get('cadastralGroup');
        $user = $request->get('user');

        $data = $this->forecastLogService->getForecastData($cadastralGroupId, $user);

        return response()->json($data);
    }
}
