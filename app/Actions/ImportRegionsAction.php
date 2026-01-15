<?php

namespace App\Actions;

use App\Enums\Resources;
use App\Models\Region;
use App\Models\User;
use App\Notifications\ImportCompletedNotification;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Lorisleiva\Actions\Concerns\AsAction;

class ImportRegionsAction
{
    use AsAction;

    public string $commandSignature = 'import:regions';

    public string $commandDescription = 'Import regions from CSV file using bulk upserts';

    private const CHUNK_SIZE = 50; // Smaller chunks for regions

    public int $regionsCount = 0;

    /**
     * Handle the bulk import of regions.
     */
    public function handle(bool $isCommand = false, ?Command $command = null): void
    {
        $this->logOrOutput($isCommand, $command, 'info', 'Starting regions import...');

        try {
            $filePath = storage_path('app/public/regions.csv');

            if (! file_exists($filePath)) {
                throw new Exception("Regions CSV file not found at: {$filePath}");
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
                $progressBar->setMessage('Processing regions...');
                $progressBar->start();
            }

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
                ->each(function ($chunk) use ($isCommand, $command, $progressBar) {
                    $this->processRegionsChunk($chunk, $isCommand, $command, $progressBar);
                });

            if ($progressBar) {
                $progressBar->setMessage('Completed');
                $progressBar->finish();
                $command->newLine();
            }

            $this->logOrOutput($isCommand, $command, 'info', "Regions import completed. Imported/updated {$this->regionsCount} regions.");
            $this->notifyUser(Resources::REGIONS, $this->regionsCount);

        } catch (Exception $e) {
            $this->logOrOutput($isCommand, $command, 'error', "Regions import failed: {$e->getMessage()}");
            Log::channel('imports')->error('Regions import failed', [
                'error' => $e->getMessage(),
                'count' => $this->regionsCount,
            ]);
            throw $e;
        }
    }

    /**
     * Process a chunk of regions using bulk upsert.
     */
    private function processRegionsChunk($chunk, bool $isCommand, ?Command $command, $progressBar = null): void
    {
        $regions = $chunk->map(function ($row) {
            // CSV structure: Codice Regione;Denominazione Regione
            [$id, $name] = $row;

            return [
                'id' => (int) $id,
                'name' => trim($name),
                'code' => sprintf('%02d', (int) $id), // Generate a 2-digit code based on ID
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->unique('id')->values(); // Remove duplicates by ID

        if ($regions->isNotEmpty()) {
            // Use upsert for bulk insert/update
            Region::query()->upsert(
                $regions->toArray(),
                ['id'], // Unique columns
                ['name', 'code', 'updated_at'] // Columns to update
            );

            $this->regionsCount += $regions->count();

            if ($progressBar) {
                $progressBar->advance($chunk->count());
                $progressBar->setMessage("Processed {$this->regionsCount} regions");
            } else {
                $this->logOrOutput($isCommand, $command, 'info', "Processed {$regions->count()} regions (Total: {$this->regionsCount})");
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
