<?php

namespace App\Jobs;

use App\Enums\Resources;
use App\Models\City;
use App\Models\User;
use App\Notifications\ImportCompletedNotification;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImportCitiesJob implements ShouldQueue
{
    use Batchable, Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $cityData;

    protected int $regionId;

    protected int $provinceId;

    public function __construct(array $cityData, int $regionId, int $provinceId)
    {
        $this->cityData = $cityData;
        $this->regionId = $regionId;
        $this->provinceId = $provinceId;
    }

    public function handle(): void
    {
        try {
            Log::channel('imports')->info("Importing city {$this->cityData['name']} ({$this->cityData['cadastral_code_csv']}) for province {$this->provinceId} and region {$this->regionId}.");

            City::updateOrCreate(
                [
                    'cadastral_code' => $this->cityData['cadastral_code_csv'],
                ],
                [
                    'name' => $this->cityData['name'],
                    'province_id' => $this->provinceId,
                    'region_id' => $this->regionId,
                ]
            );

            // Notify admin after the job completes
            $user = Auth::user() ?? User::admins()->first();
            if ($user) {
                $user->notify(new ImportCompletedNotification(Resources::CITIES, 1));
            } else {
                Log::channel('imports')->info('City import completed but no user to notify.');
            }

            Log::channel('imports')->info("Processed city {$this->cityData['name']} successfully.");
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('imports')->error("City upsert failed for {$this->cityData['name']} ({$this->cityData['province_code_csv']}): {$e->getMessage()}");
            $this->fail($e);
        }
    }
}
