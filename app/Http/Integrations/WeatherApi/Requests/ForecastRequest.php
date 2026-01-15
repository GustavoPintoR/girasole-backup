<?php

namespace App\Http\Integrations\WeatherApi\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ForecastRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * @param string|array $coordinates Either "lat,lon" or array of "lat,lon"
     * @param array        $parameters  Up to 10 Meteomatics parameters
     */
    public function __construct(
        public string|array $coordinates,
        public array $parameters,
    ) {}

    public function resolveEndpoint(): string
    {
        $addDays = config('girasole.weather_forecast.fetch_days');
        $start = now('UTC')->format('Y-m-d\TH:i:00.000\+01:00');
        $end = now('UTC')->addDays($addDays)->format('Y-m-d\TH:i:00.000\+01:00');

        $timeRange  = "{$start}--{$end}:PT1H";
        $parameters = implode(',', $this->parameters);
        $location   = is_array($this->coordinates)
            ? implode('+', $this->coordinates)
            : $this->coordinates;

        return "/{$timeRange}/{$parameters}/{$location}/json";
    }
}
