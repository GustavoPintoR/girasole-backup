<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $isAllDay = $this->faker->boolean(40); // 40% all-day
        $title = $this->faker->sentence(3);

        if ($isAllDay) {
            $startDate = Carbon::today()->addDays($this->faker->numberBetween(-30, 30));
            $durationDays = $this->faker->numberBetween(1, 3);
            $endDate = (clone $startDate)->addDays($durationDays);

            $start = Carbon::now('UTC')
                ->addDays($this->faker->numberBetween(-30, 30))
                ->setTime($this->faker->numberBetween(8, 18), [0, 15, 30, 45][array_rand([0, 1, 2, 3])]);

            return [
                'all_day' => true,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'start' => $start,
                'end' => null,
                'title' => $title,
                'description' => $this->faker->optional()->paragraph(),
            ];
        }

        $start = Carbon::now('UTC')
            ->addDays($this->faker->numberBetween(-30, 30))
            ->setTime($this->faker->numberBetween(8, 18), [0, 15, 30, 45][array_rand([0, 1, 2, 3])]);

        $end = (clone $start)->addMinutes($this->faker->numberBetween(30, 180));

        return [
            'all_day' => false,
            'start' => $start,
            'end' => $end,
            'start_date' => null,
            'end_date' => null,
            'title' => $title,
            'description' => $this->faker->optional()->paragraph(),
        ];
    }
}
