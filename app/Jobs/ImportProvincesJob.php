<?php

namespace App\Jobs;

use App\Enums\Resources;
use App\Models\Province;
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

class ImportProvincesJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $provinceData;

    protected int $regionId;

    public function __construct(array $provinceData, int $regionId)
    {
        $this->provinceData = $provinceData;
        $this->regionId = $regionId;
    }

    public function handle(): void
    {
        try {
            Log::channel('imports')->info("Importing province {$this->provinceData['name']} ({$this->provinceData['code']}) for region {$this->regionId}.");

            Province::updateOrCreate(
                ['code' => $this->provinceData['code']],
                [
                    'name' => $this->provinceData['name'],
                    'region_id' => $this->regionId,
                ]
            );

            // Notify admin after the job completes
            $user = Auth::user() ?? User::admins()->first();
            if ($user instanceof User) {
                $user->notify(new ImportCompletedNotification(Resources::PROVINCES, 1));
            } else {
                Log::channel('imports')->info('Province import completed but no user to notify.');
            }

            Log::channel('imports')->info("Processed province {$this->provinceData['name']} successfully.");
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('imports')->error("Province upsert failed for {$this->provinceData['name']} ({$this->provinceData['code']}): {$e->getMessage()}");
            $this->fail($e);
        }
    }
}
