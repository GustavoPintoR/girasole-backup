<?php

namespace App\Console\Commands;

use App\Services\WeatherApi\WeatherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Saloon\Exceptions\InvalidPoolItemException;

class RunWeatherForecast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:forecast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch daily weather forecasts';

    /**
     * Execute the console command.
     * @throws InvalidPoolItemException
     */
    public function handle(WeatherService $service): void
    {
        if (config('girasole.weather_forecast.enable')) {
            $this->info('Starting weather forecast batch…');
            $service->runBatch();
            $this->info('Batch finished.');
        } else {
            $this->info("Weather forecast is disabled");
        }
    }
}
