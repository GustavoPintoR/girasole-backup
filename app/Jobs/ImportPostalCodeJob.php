<?php

namespace App\Jobs;

use App\Models\PostalCode;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportPostalCodeJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $postalCodeData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $postalCodeData)
    {
        $this->postalCodeData = $postalCodeData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $item = $this->postalCodeData;

        $postalCode = PostalCode::updateOrCreate(
            ['code' => $item['code']],
            [
                'code' => $item['code'],
            ]
        );

        if ($item['city_id']) {
            if (! $postalCode->cities()->where('city_id', $item['city_id'])->exists()) {
                $postalCode->cities()->attach($item['city_id'], [
                    'zone' => $item['zone'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::info("Attached city ID {$item['city_id']} to postal code {$item['code']}.");
            } else {
                Log::info("City ID {$item['city_id']} already attached to postal code {$item['code']}.");
            }
        } else {
            Log::warning("No city ID available for postal code {$item['code']}.");
        }
    }
}
