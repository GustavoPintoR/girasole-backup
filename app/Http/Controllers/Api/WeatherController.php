<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    /**
     * Fetch current weather, humidity, and precipitation for given coordinates.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentWeather(Request $request)
    {
        if (!config('girasole.dashboard.show_weather')) {
            return response()->json([
                'error' => 'Weather data is not enabled.'
            ]);
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'timezone' => 'string|nullable',
        ]);

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $timezone = $validated['timezone'] ?? 'auto';

        $cacheKey = "weather:{$latitude}:{$longitude}";

        $weatherData = Cache::remember($cacheKey, 3600, function () use ($latitude, $longitude, $timezone) {
            $url = 'https://api.open-meteo.com/v1/forecast?' .
                http_build_query([
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'current' => 'temperature_2m,precipitation,relative_humidity_2m',
                    'timezone' => $timezone,
                ]);

            $response = Http::get($url);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();
            $current = $data['current'] ?? null;

            if (!$current) {
                return null;
            }

            return [
                'temperature' => $current['temperature_2m'],
                'precipitation' => $current['precipitation'],
                'humidity' => $current['relative_humidity_2m'],
                'time' => $current['time'],
                'units' => [
                    'temperature' => $data['current_units']['temperature_2m'],
                    'precipitation' => $data['current_units']['precipitation'],
                    'humidity' => $data['current_units']['relative_humidity_2m'],
                ],
                'location' => [
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'timezone' => $data['timezone'],
                ],
            ];
        });

        if (!$weatherData) {
            return response()->json([
                'error' =>__('ui.failed_to_fetch_weather_data'),
            ], 503);
        }

        return response()->json($weatherData);
    }
}
