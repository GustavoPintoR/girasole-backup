<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class InfluxDbProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $config = config('services.influxdb');
        $bucket = Arr::get($config, 'bucket');
        $this->app->singleton($bucket, function ($app) {
            $config = $app['config']['services.influxdb'];

            return new \InfluxDB2\Client([
                'url' => $config['host'],
                'token' => $config['token'],
                'bucket' => $config['bucket'],
                'org' => $config['org'],
                'precision' => \InfluxDB2\Model\WritePrecision::S,
            ]);
        });
    }
}
