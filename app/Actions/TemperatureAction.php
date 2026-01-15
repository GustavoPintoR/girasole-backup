<?php

namespace App\Actions;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use InfluxDB2\Point;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;

class TemperatureAction
{
    use AsAction, AsCommand;

    public string $commandSignature = 'girasole:temperature {location}';

    public string $commandDescription = 'Generates random temperatures';

    public function asCommand(Command $command): void
    {
        $this->handle($command->argument('location'));
        $command->info('Temperature data generated successfully.');
    }

    public function handle($location = 'indoor'): void
    {
        $config = config('services.influxdb');
        $bucket = Arr::get($config, 'bucket');
        $writeApi = app($bucket)->createWriteApi();

        $hours = 12;
        $minTemp = 18;
        $maxTemp = 28;
        $now = now();

        for ($i = 0; $i < $hours; $i++) {
            // Sine wave: peak at midday (hour 6), trough at midnight (hour 0/12)
            $angle = ($i / ($hours - 1)) * pi(); // 0 to pi
            $baseTemp = $minTemp + ($maxTemp - $minTemp) * sin($angle);
            $noise = rand(-100, 100) / 100.0; // ±1°C random noise
            $temp = round($baseTemp + $noise, 1);

            $point = Point::measurement('temperature')
                ->addTag('location', $location)
                ->addField('value', (int) $temp)
                ->time($now->copy()->addHours($i)->timestamp);
            $writeApi->write($point);
        }

    }
}
