<?php

namespace App\Actions;

use App\Enums\Resources;
use App\Models\City;
use App\Models\Province;
use App\Models\User;
use App\Notifications\ImportCompletedNotification;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Lorisleiva\Actions\Concerns\AsAction;

class ImportCitiesAction
{
    use AsAction;

    public string $commandSignature = 'import:cities';

    public string $commandDescription = 'Import cities from CSV file using bulk upserts';

    private const CHUNK_SIZE = 1000;

    public int $citiesCount = 0;

    /**
     * Handle the bulk import of cities.
     */
    public function handle(bool $isCommand = false, ?Command $command = null): void
    {
        $this->logOrOutput($isCommand, $command, 'info', 'Starting cities import...');

        try {
            $filePath = storage_path('app/public/cities.csv');
            // if(app()->environment('testing')){
            //     $filePath = storage_path('app/imports/cities.csv');
            // }

            if (! file_exists($filePath)) {
                throw new Exception("Cities CSV file not found at: {$filePath}");
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
                $progressBar->setMessage('Processing cities...');
                $progressBar->start();
            }

            // Get province lookup for performance
            $provinces = Province::query()->pluck('id', 'code')->toArray();

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
                ->each(function ($chunk) use ($provinces, $isCommand, $command, $progressBar) {
                    $this->processCitiesChunk($chunk, $provinces, $isCommand, $command, $progressBar);
                });

            if ($progressBar) {
                $progressBar->setMessage('Completed');
                $progressBar->finish();
                $command->newLine();
            }

            $this->logOrOutput($isCommand, $command, 'info', "Cities import completed. Imported/updated {$this->citiesCount} cities.");
            $this->notifyUser(Resources::CITIES, $this->citiesCount);

        } catch (Exception $e) {
            $this->logOrOutput($isCommand, $command, 'error', "Cities import failed: {$e->getMessage()}");
            Log::channel('imports')->error('Cities import failed', [
                'error' => $e->getMessage(),
                'count' => $this->citiesCount,
            ]);
            throw $e;
        }
    }

    /**
     * Process a chunk of cities using bulk upsert.
     */
    private function processCitiesChunk($chunk, array $provinces, bool $isCommand, ?Command $command, $progressBar = null): void
    {
        $cities = $chunk->map(function ($row) use ($provinces) {
            // CSV structure: Codice Catastale del comune;Denominazione in italiano;Sigla automobilistica;Codice Regione
            [$cadastralCode, $name, $provinceCode, $regionId] = $row;

            if (! isset($provinces[$provinceCode])) {
                Log::channel('imports')->warning("Province not found for code: {$provinceCode}");

                return null;
            }

            return [
                'name' => trim($name),
                'province_id' => $provinces[$provinceCode],
                'region_id' => (int) $regionId,
                'cadastral_code' => trim($cadastralCode),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->filter()->values();

        if ($cities->isNotEmpty()) {
            // Use upsert for bulk insert/update
            City::query()->upsert(
                $cities->toArray(),
                ['name', 'cadastral_code'], // Unique columns - match database constraint
                ['province_id', 'region_id', 'updated_at'] // Columns to update
            );

            $this->citiesCount += $cities->count();

            if ($progressBar) {
                $progressBar->advance($chunk->count());
                $progressBar->setMessage("Processed {$this->citiesCount} cities");
            } else {
                $this->logOrOutput($isCommand, $command, 'info', "Processed {$cities->count()} cities (Total: {$this->citiesCount})");
            }
        }
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
    private function logOrOutput(bool $isCommand, ?Command $command, string $method, string $message): void
    {
        if ($isCommand && $command) {
            $command->$method($message);
        } else {
            $logMethod = match ($method) {
                'info', 'line' => 'info',
                'warn' => 'warning',
                'error' => 'error',
                default => 'info',
            };
            Log::channel('imports')->$logMethod($message);
        }
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
