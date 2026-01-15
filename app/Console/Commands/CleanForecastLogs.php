<?php

namespace App\Console\Commands;

use App\Models\ForecastLog;
use App\Models\ForecastSetup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanForecastLogs extends Command
{
    protected $signature = 'forecast:clean-logs';

    protected $description = 'Daily job: delete old forecast logs based on per-field retention policy';

    public function handle(): int
    {
        $this->info("[Forecast Cleanup] Starting daily retention cleanup...");
        Log::channel('weather_api')->info('[Forecast Cleanup] Daily retention cleanup started');
        $today = Carbon::today();
        $totalDeleted = 0;
        $fieldsAffected = 0;

        $setups = ForecastSetup::whereNotNull('retention')
            ->where('retention', '>', 0)
            ->select('field_id', 'retention')
            ->get();

        if ($setups->isEmpty()) {
            $this->info("[Forecast Cleanup] No forecast setups found.");
            Log::channel('weather_api')->info('[Forecast Cleanup] No retention policies defined → nothing to do');
            return self::SUCCESS;
        }

        $this->info("[Forecast Cleanup] Forecast setups found. {$setups->count()}");
        Log::channel('weather_api')->info('[Forecast Cleanup] Forecast setups found. {$setups->count()}');

        foreach ($setups as $setup) {
            $cutoff = $today->clone()->subDays((int) $setup->retention);

            $deleted = ForecastLog::where('field_id', $setup->field_id)
                ->whereDate('ran_at', '<', $cutoff)
                ->delete();

            if ($deleted > 0) {
                $fieldsAffected++;
                $totalDeleted += $deleted;

                Log::channel('weather_api')->info('[Forecast Cleanup] Deleted old logs', [
                    'field_id'       => $setup->field_id,
                    'retention_days' => $setup->retention,
                    'cutoff_date'    => $cutoff->toDateString(),
                    'deleted_count'  => $deleted,
                ]);
            }
        }

        Log::channel('weather_api')->info('[Forecast Cleanup] Daily cleanup finished', [
            'total_logs_deleted' => $totalDeleted,
            'fields_affected'    => $fieldsAffected,
            'run_at'             => now()->toDateTimeString(),
        ]);

        return self::SUCCESS;
    }
}
