<?php

namespace App\Traits;

use Illuminate\Queue\Middleware\RateLimited;

trait HasRateLimiter
{
    public int $tries = 3;

    public int $backoff = 10;

    /**
     * Get the middleware the notification job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(object $notifiable, string $channel): array
    {
        return match ($channel) {
            'mail' => [new RateLimited('notifications')],
            default => [],
        };
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array<string, string>
     */
    public function viaQueues(): array
    {
        return [
            'database' => 'default',
            'mail' => 'notifications',
        ];
    }
}
