<?php

namespace Database\Seeders;

use App\Models\CadastralGroup;
use App\Models\ForecastLog;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class ForecastLogSeeder extends Seeder
{
    public function run(): void
    {
        $fieldIds = CadastralGroup::query()
            ->orderBy('id')
            ->limit(10)
            ->pluck('id')
            ->toArray();

        if (empty($fieldIds)) {
            $this->command->error('No CadastralGroup records found! Seed them first.');
            return;
        }

        $this->command->info('Seeding ForecastLogs for this week using first 10 fields...');

        $faker = FakerFactory::create();
        $startOfWeek = Carbon::now()->startOfWeek();
        $logsPerDay = 50;

        for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
            $date = $startOfWeek->copy()->addDays($dayOffset);
            $isWeekend = in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);

            $failureChance = $isWeekend ? 0.35 : 0.12;
            if ($date->day == 5) $failureChance = 0.45;

            for ($i = 0; $i < $logsPerDay; $i++) {
                $status = $faker->boolean($failureChance * 100) ? 'failed' : 'success';

                $log = ForecastLog::create([
                    'field_id' => Arr::random($fieldIds),
                    'status'   => $status,
                    'ran_at' => $date->copy(),
                    'data'     => $this->generateFakeData($faker, $date),
                    'created_at' => $date->copy()->addHours($faker->numberBetween(0, 23))->addMinutes($faker->numberBetween(0, 59)),
                    'updated_at' => now(),
                ]);

                $log->touch();
            }

        }

        $this->command->info('Done! 350 ForecastLogs seeded for this week.');
    }

    private function generateFakeData($faker, Carbon $date): array
    {
        $isBadWeatherDay = $date->day == 7;

        return [
            'forecast_date'   => $date->format('Y-m-d'),
            'temperature'     => $isBadWeatherDay
                ? $faker->randomFloat(2, -5, 15)
                : $faker->randomFloat(2, 5, 25),
            'precipitation'   => $isBadWeatherDay
                ? $faker->randomFloat(2, 50, 200)
                : $faker->randomFloat(2, 0, 40),
            'humidity'        => $faker->numberBetween(60, 98),
            'wind_speed'      => $isBadWeatherDay
                ? $faker->randomFloat(2, 30, 100)
                : $faker->randomFloat(2, 0, 40),
            'pressure'        => $faker->randomFloat(2, 980, 1020),
            'crop_yield_est'  => $faker->randomFloat(2, 2000, 7500),
            'notes'           => $isBadWeatherDay
                ? $faker->randomElement(['Storm alert', 'Heavy rain expected', 'High winds', 'Forecast uncertain'])
                : $faker->optional(0.3)->sentence(),
            'source'          => $faker->randomElement(['NOAA', 'ECMWF', 'OpenWeather', 'Custom Model']),
            'confidence'      => $isBadWeatherDay
                ? $faker->randomFloat(2, 0.55, 0.78)
                : $faker->randomFloat(2, 0.80, 0.99),
        ];
    }
}
