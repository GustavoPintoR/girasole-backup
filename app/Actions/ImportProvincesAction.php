<?php

namespace App\Actions;

use App\Enums\Resources;
use App\Models\Province;
use App\Models\Region;
use App\Models\User;
use App\Notifications\ImportCompletedNotification;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Lorisleiva\Actions\Concerns\AsAction;

class ImportProvincesAction
{
    use AsAction;

    public string $commandSignature = 'import:provinces';

    public string $commandDescription = 'Import provinces from CSV file using bulk upserts';

    private const CHUNK_SIZE = 500;

    public int $provincesCount = 0;

    /**
     * Handle the bulk import of provinces.
     */
    public function handle(bool $isCommand = false, ?Command $command = null): void
    {
        $this->logOrOutput($isCommand, $command, 'info', 'Starting provinces import...');

        try {
            $filePath = storage_path('app/public/provinces.csv');

            if (! file_exists($filePath)) {
                throw new Exception("Provinces CSV file not found at: {$filePath}");
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
                $progressBar->setMessage('Processing provinces...');
                $progressBar->start();
            }

            // Get region lookup for performance
            $regions = Region::query()->pluck('id', 'id')->toArray();

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
                ->each(function ($chunk) use ($regions, $isCommand, $command, $progressBar) {
                    $this->processProvincesChunk($chunk, $regions, $isCommand, $command, $progressBar);
                });

            if ($progressBar) {
                $progressBar->setMessage('Completed');
                $progressBar->finish();
                $command->newLine();
            }

            $this->logOrOutput($isCommand, $command, 'info', "Provinces import completed. Imported/updated {$this->provincesCount} provinces.");

            $this->notifyUser(Resources::PROVINCES, $this->provincesCount);

        } catch (Exception $e) {
            $this->logOrOutput($isCommand, $command, 'error', "Provinces import failed: {$e->getMessage()}");
            Log::channel('imports')->error('Provinces import failed', [
                'error' => $e->getMessage(),
                'count' => $this->provincesCount,
            ]);
            throw $e;
        }
    }

    /**
     * Process a chunk of provinces using bulk upsert.
     */
    private function processProvincesChunk($chunk, array $regions, bool $isCommand, ?Command $command, $progressBar = null): void
    {
        $provinces = $chunk->map(function ($row) use ($regions) {
            // CSV structure: Denominazione;Sigla automobilistica;Codice Regione
            [$name, $code, $regionId] = $row;

            if (! isset($regions[$regionId])) {
                Log::channel('imports')->warning("Region not found for ID: {$regionId}");

                return null;
            }

            return [
                'code' => trim($code),
                'name' => trim($name),
                'region_id' => (int) $regionId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->filter()->unique('code')->values(); // Remove duplicates by code

        if ($provinces->isNotEmpty()) {
            // Use upsert for bulk insert/update
            Province::query()->upsert(
                $provinces->toArray(),
                ['name', 'code'], // Unique columns
                ['region_id', 'updated_at'] // Columns to update
            );

            $this->provincesCount += $provinces->count();

            if ($progressBar) {
                $progressBar->advance($chunk->count());
                $progressBar->setMessage("Processed {$this->provincesCount} provinces");
            } else {
                $this->logOrOutput($isCommand, $command, 'info', "Processed {$provinces->count()} provinces (Total: {$this->provincesCount})");
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
