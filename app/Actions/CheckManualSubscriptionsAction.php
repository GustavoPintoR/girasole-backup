<?php

namespace App\Actions;

use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;

class CheckManualSubscriptionsAction
{
    use AsAction, AsCommand;

    public string $commandSignature = 'subscriptions:disable-expired-manual-subs';

    public string $commandDescription = 'Deactive manual subscriptions that has expired already';

    public function handle()
    {
        $subscriptions = Subscription::where('type', 'manual')
            ->where('stripe_status', 'active')
            ->whereNotNull('ends_at')
            ->get();

        Log::channel('subscriptions')->info('Starting check for expired manual subscriptions', [
            'total_found' => $subscriptions->count(),
        ]);

        $expiredCount = 0;

        foreach ($subscriptions as $subscription) {
            if ($subscription->ends_at->isPast()) {
                $subscription->update([
                    'stripe_status' => 'canceled',
                ]);

                $expiredCount++;

                Log::channel('subscriptions')->info('Manual subscription marked as expired', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'ended_at' => $subscription->ends_at,
                ]);
            }
        }

        Log::channel('subscriptions')->info('Expired manual subscription check completed', [
            'expired_count' => $expiredCount,
        ]);
    }

    public function asCommand($command)
    {
        $command->info('Checking for expired manual subscriptions...');

        $this->handle();

        $command->info('Expired manual subscriptions check completed.');
        Log::channel('subscriptions')->info('Manual subscription command executed from console');
    }
}
