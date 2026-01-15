<?php

namespace App\Actions;

use App\Enums\Resources;
use App\Models\City;
use App\Models\User;
use App\Notifications\ImportCompletedNotification;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Lorisleiva\Actions\Concerns\AsAction;

class ImportPostalCodesAction
{
    use AsAction;

    public string $commandSignature = 'import:postal-codes';

    public string $commandDescription = 'Import postal codes from CSV file using bulk upserts';

    private const CHUNK_SIZE = 2000; // Larger chunk for postal codes

    public int $postalCodesCount = 0;

    /**
     * Handle the bulk import of postal codes.
     */
    public function handle(bool $isCommand = false, ?Command $command = null): void
    {
        $this->logOrOutput($isCommand, $command, 'info', 'Starting postal codes import...');

        try {
            $filePath = storage_path('app/public/postal_codes.csv');

            if (! file_exists($filePath)) {
                throw new Exception("Postal codes CSV file not found at: {$filePath}");
            }

            // Count total lines for progress bar
            $totalLines = 0;
            if ($isCommand && $command) {
                $handle = fopen($filePath, 'r');
                while (fgets($handle) !== false) {
                    $totalLines++;
                }
                fclose($handle);
                $totalLines--; // Subtract header row
            }

            $progressBar = null;
            if ($isCommand && $command && $totalLines > 0) {
                $progressBar = $command->getOutput()->createProgressBar($totalLines);
                $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
                $progressBar->setMessage('Processing postal codes...');
                $progressBar->start();
            }

            // Get city lookup for performance - using cadastral code
            $cities = City::query()->pluck('id', 'cadastral_code')->toArray();

            // Process CSV in chunks for memory efficiency
            LazyCollection::make(function () use ($filePath) {
                $handle = fopen($filePath, 'r');

                // Skip header row
                fgetcsv($handle, 0, ';');

                while (($row = fgetcsv($handle, 0, ';')) !== false) {
                    yield $row;
                }

                fclose($handle);
            })
                ->chunk(self::CHUNK_SIZE)
                ->each(function ($chunk) use ($cities, $isCommand, $command, $progressBar) {
                    $this->processPostalCodesChunk($chunk, $cities, $isCommand, $command, $progressBar);
                });

            if ($progressBar) {
                $progressBar->setMessage('Completed');
                $progressBar->finish();
                $command->newLine();
            }

            $this->logOrOutput($isCommand, $command, 'info', "Postal codes import completed. Imported/updated {$this->postalCodesCount} postal codes.");
            $this->notifyUser(Resources::POSTAL_CODES, $this->postalCodesCount);

        } catch (Exception $e) {
            $this->logOrOutput($isCommand, $command, 'error', "Postal codes import failed: {$e->getMessage()}");
            Log::channel('imports')->error('Postal codes import failed', [
                'error' => $e->getMessage(),
                'count' => $this->postalCodesCount,
            ]);
            throw $e;
        }
    }

    /**
     * Process a chunk of postal codes using bulk upsert.
     */
    private function processPostalCodesChunk($chunk, array $cities, bool $isCommand, ?Command $command, $progressBar = null): void
    {
        // Collect postal codes and relationships
        $postalCodes = [];
        $relationships = [];

        foreach ($chunk as $row) {
            // CSV structure: cap;codice_belfiore
            [$code, $cadastralCode] = $row;

            if (! isset($cities[$cadastralCode])) {
                Log::channel('imports')->warning("City not found for cadastral code: {$cadastralCode}");

                continue;
            }

            $trimmedCode = str_pad(trim($code), 5, '0', STR_PAD_LEFT);
            $cityId = $cities[$cadastralCode];

            // Collect postal codes for bulk upsert
            $postalCodes[$trimmedCode] = [
                'code' => $trimmedCode,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Collect relationships for bulk insert (city_id, postal_code will be resolved)
            $relationships[] = [
                'code' => $trimmedCode,
                'city_id' => $cityId,
            ];
        }

        if (empty($postalCodes)) {
            return;
        }

        DB::transaction(function () use ($postalCodes, $relationships, $isCommand, $command, $progressBar, $chunk) {
            // Use raw SQL with RETURNING to get IDs efficiently
            $postalCodesData = array_values($postalCodes);
            $now = now()->format('Y-m-d H:i:s');

            // Build the upsert query with RETURNING clause
            $codes = collect($postalCodesData)->pluck('code')->map(fn ($code) => "'{$code}'")->join(',');
            $values = collect($postalCodesData)
                ->map(fn ($item) => "('{$item['code']}', '{$now}', '{$now}')")
                ->join(',');

            $sql = "
                INSERT INTO postal_codes (code, created_at, updated_at) 
                VALUES {$values}
                ON CONFLICT (code) DO UPDATE SET updated_at = EXCLUDED.updated_at
                RETURNING id, code
            ";

            // Execute and get postal code IDs
            $results = DB::select($sql);
            $postalCodeIds = collect($results)->pluck('id', 'code')->toArray();

            // Prepare pivot data using the returned IDs
            $pivotData = [];
            foreach ($relationships as $relationship) {
                $postalCodeId = $postalCodeIds[$relationship['code']] ?? null;

                if ($postalCodeId) {
                    $pivotData[] = [
                        'city_id' => $relationship['city_id'],
                        'postal_code_id' => $postalCodeId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Bulk upsert relationships
            if (! empty($pivotData)) {
                DB::table('city_postal_code')->upsert(
                    $pivotData,
                    ['city_id', 'postal_code_id'], // Unique constraint
                    ['updated_at'] // Update timestamp only
                );
            }

            $this->postalCodesCount += count($postalCodes);

            if ($progressBar) {
                $progressBar->advance($chunk->count());
                $progressBar->setMessage("Processed {$this->postalCodesCount} postal codes");
            } else {
                $this->logOrOutput($isCommand, $command, 'info', 'Processed '.count($postalCodes)." postal codes (Total: {$this->postalCodesCount})");
            }
        });
    }

    /**
     * Execute the action as a console command.
     */
    public function asCommand(Command $command): void
    {
        $this->handle(true, $command);
    }

    /**
     * Helper method to log or output messages based on context.
     */
    private function logOrOutput(bool $isCommand, ?Command $command, string $level, string $message): void
    {
        if ($isCommand && $command) {
            $command->line($message);
        }

        Log::channel('imports')->{$level}($message);
    }

    /**
     * Notify admin user upon completion.
     */
    private function notifyUser(Resources $resource, int $count): void
    {
        $user = Auth::user() ?? User::admins()->first();
        if ($user instanceof User && $count > 0) {
            $user->notify(new ImportCompletedNotification($resource, $count));
        } else {
            Log::channel('imports')->info("{$resource->value} import completed but no user to notify.");
        }
    }
}
