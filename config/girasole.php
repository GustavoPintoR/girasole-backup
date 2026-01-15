<?php

return [
    'dashboard' => [
        'show_sensors' => env('SHOW_SENSORS', true),
        'show_weather' => env('SHOW_WEATHER', false),
    ],
    'posthog' => [
        'api_key' => env('POSTHOG_KEY', null),
        'host' => env('POSTHOG_HOST', 'https://app.posthog.com'),
        'enabled' => env('POSTHOG_ENABLED', false),
    ],
    'scribe' => [
        'username' => env('SCRIBE_AUTH_USERNAME', 'adminpass'),
        'password' => env('SCRIBE_AUTH_PASSWORD', 'secretpass'),
    ],
    'weather_forecast' => [
        'enable' => env('WEATHER_FORECAST_ENABLED', true),
        'api' => 'https://api.meteomatics.com',
        'username' => env('METEOMATICS_USERNAME', 'girasole_farm'),
        'password' => env('METEOMATICS_PASSWORD', 'EXQDkZSUCuRXw2PS'),
        'batch_mode' => false,
        'fetch_days' => 10,
        'calls' => [
            'monday' => 250,
            'tuesday' => 250,
            'wednesday' => 250,
            'thursday' => 250,
            'friday' => 250,
            'saturday' => 250,
            'sunday' => 250,
        ],

        'delay_ms' => env('WEATHER_POOL_DELAY_MS', 500),
    ]
];
