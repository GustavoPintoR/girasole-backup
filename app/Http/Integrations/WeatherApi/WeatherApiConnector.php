<?php

namespace App\Http\Integrations\WeatherApi;

use Illuminate\Support\Facades\Log;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\PredisStore;

class WeatherApiConnector extends Connector
{
    use AcceptsJson, HasRateLimits;

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return config('girasole.weather_forecast.api');
    }

    /**
     * @return BasicAuthenticator
     */
    protected function defaultAuth(): BasicAuthenticator
    {
        return new BasicAuthenticator(
            config('girasole.weather_forecast.username'),
            config('girasole.weather_forecast.password')
        );
    }

    protected function defaultDelay(): ?int
    {
        return config('girasole.weather_forecast.delay_ms');
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [
            'timeout' => 60,
        ];
    }

    /**
     * Resolve the rate limits for the application.
     *
     * @return array
     */
    protected function resolveLimits(): array
    {
        return [
            Limit::allow(8)->everyMinute()->sleep(),
        ];
    }

    /**
     * Handle the exceeded limit
     *
     * If the limit should wait, we will increment a delay - otherwise we will continue
     *
     * @throws \Saloon\RateLimitPlugin\Exceptions\RateLimitReachedException
     */
    protected function handleExceededLimit(Limit $limit, PendingRequest $pendingRequest): void
    {
        if (! $limit->getShouldSleep()) {
            $this->throwLimitException($limit);
        }

        $remainingSeconds = $limit->getRemainingSeconds();
        $hits = $limit->getHits();
        $allow = $limit->getAllow();

        Log::channel('weather_api')->warning(
            'Meteomatics rate limit enforced: delaying request to comply with 10 requests/minute',
            [
                'action'              => 'rate_limit_delay_applied',
                'current_hits'        => $hits,
                'allowed_hits'        => $allow,
                'delay_seconds'       => $remainingSeconds,
                'delay_milliseconds'  => $remainingSeconds * 1000,
                'limit_name'          => $limit->getName(),
                'timestamp'           => now()->toDateTimeString(),
            ]
        );

        $existingDelay = $pendingRequest->delay()->get() ?? 0;
        $remainingMilliseconds = $remainingSeconds * 1000;

        $pendingRequest->delay()->set($existingDelay + $remainingMilliseconds);
    }

    /**
     * Resolves and returns the rate limit store implementation.
     *
     * @return RateLimitStore
     */
    protected function resolveRateLimitStore(): RateLimitStore
    {
        $client = new \Predis\Client([
            'port'   => 6379,
        ]);

        return new PredisStore($client);
    }
}
