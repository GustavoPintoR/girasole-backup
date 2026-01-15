<?php

namespace App\Actions;

use App\Enums\Resources;
use App\Models\User;
use App\Notifications\ImportCompletedNotification;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class ImportAllAction
{
    use AsAction;

    public string $commandSignature = 'import:all';

    public string $commandDescription = 'Import regions, provinces, cities, and postal codes from CSV files sequentially using pipeline';

    /**
     * Handle the sequential import of all resources using pipeline.
     *
     * @param  bool  $isCommand  Whether the method is called from a command
     * @param  Command|null  $command  The console command instance
     *
     * @throws Exception
     */
    public function handle(bool $isCommand = false, ?Command $command = null): void
    {
        $this->logOrOutput($isCommand, $command, 'info', 'Starting sequential import pipeline...');

        $importContext = [
            'isCommand' => $isCommand,
            'command' => $command,
            'counts' => [
                Resources::REGIONS->value => 0,
                Resources::PROVINCES->value => 0,
                Resources::CITIES->value => 0,
                Resources::POSTAL_CODES->value => 0,
            ],
        ];

        try {
            $result = app(Pipeline::class)
                ->send($importContext)
                ->through([
                    ImportRegionsPipe::class,
                    ImportProvincesPipe::class,
                    ImportCitiesPipe::class,
                    ImportPostalCodesPipe::class,
                ])
                ->then(function ($context) use ($isCommand, $command) {
                    $this->logOrOutput($isCommand, $command, 'info', 'All imports completed successfully via pipeline.');

                    return $context;
                });

            // Send notifications for all resources
            $user = Auth::user() ?? User::admins()->first();
            if ($user instanceof User) {
                foreach ($result['counts'] as $resource => $count) {
                    if ($count > 0) {
                        $user->notify(new ImportCompletedNotification($this->getResource($resource), $count));
                    }
                }
            } else {
                $this->logOrOutput($isCommand, $command, 'info', 'Imports completed but no user to notify.');
            }

            Log::channel('imports')->info('All imports completed successfully via pipeline', $result['counts']);

        } catch (Exception $e) {
            $this->logOrOutput($isCommand, $command, 'error', "Import pipeline failed: {$e->getMessage()}");
            Log::channel('imports')->error('Sequential import pipeline failed', [
                'error' => $e->getMessage(),
                'counts' => $importContext['counts'] ?? [],
            ]);
            throw $e;
        }
    }

    /**
     * Execute the action as a console command.
     *
     * @throws Exception
     */
    public function asCommand(Command $command): void
    {
        $this->handle(true, $command);
    }

    /**
     * Execute the action as a job.
     *
     * @throws Exception
     */
    public function asJob(): void
    {
        $this->handle();
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
     * Get the Resources enum from string value.
     */
    private function getResource(string $resourceName): Resources
    {
        return match ($resourceName) {
            Resources::REGIONS->value => Resources::REGIONS,
            Resources::PROVINCES->value => Resources::PROVINCES,
            Resources::CITIES->value => Resources::CITIES,
            Resources::POSTAL_CODES->value => Resources::POSTAL_CODES,
            default => Resources::USERS,
        };
    }
}
