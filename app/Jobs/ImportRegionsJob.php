<?php

namespace App\Jobs;

use App\Enums\Resources;
use App\Models\Region;
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

class ImportRegionsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $regionData;

    public function __construct(array $regionData)
    {
        $this->regionData = $regionData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::channel('imports')->info("Importing region {$this->regionData['name']} ({$this->regionData['code']}).");

            Region::updateOrCreate(
                ['code' => $this->regionData['code']],
                ['name' => $this->regionData['name']]
            );

            // Notify admin after the job completes
            $user = Auth::user() ?? User::admins()->first();
            if ($user instanceof User) {
                $user->notify(new ImportCompletedNotification(Resources::REGIONS, 1));
            } else {
                Log::channel('imports')->info('Region import completed but no user to notify.');
            }

            Log::channel('imports')->info("Processed region {$this->regionData['name']} successfully.");
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('imports')->error("Region upsert failed for {$this->regionData['name']} ({$this->regionData['code']}): {$e->getMessage()}");
            $this->fail($e);
        }
    }
}
