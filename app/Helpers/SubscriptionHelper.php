<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\User;
use Laravel\Cashier\Subscription;

class SubscriptionHelper
{

     public static function newSubscription(User $user, Plan $plan,int $quantity, ?string $endsAt)
    {
        //Add one period interval until expired
        $intervalCount = 1;
        
        if(!$endsAt){
            $endsAt = match ($plan->interval) {
                'day'   => Carbon::now()->addDays($intervalCount),
                'week'  => Carbon::now()->addWeeks($intervalCount),
                'month' => Carbon::now()->addMonths($intervalCount),
                'year'  => Carbon::now()->addYears($intervalCount),
                default => null,
            };
        }

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'type' => 'manual',
            'stripe_id' => 'manual_' . uniqid(),
            'stripe_status' => 'active',
            'stripe_price' => $plan->stripe_price_id,
            'quantity' => $quantity,
            'trial_ends_at' => null,
            'ends_at' => $endsAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return $subscription;
    }
}
