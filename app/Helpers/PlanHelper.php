<?php

namespace App\Helpers;

use App\Models\Plan;
use App\Models\User;
use App\Models\CadastralGroup;
use Illuminate\Http\JsonResponse;
use Laravel\Cashier\Subscription;
use Illuminate\Support\Facades\Log;

class PlanHelper
{

    /**
     * @param User $user
     * @return bool
     */
    public static function checkIfTokenIsReadOnly(User $user): bool
    {
        $abilities = $user->currentAccessToken()->abilities;
        if (in_array('read-only', $abilities) && !in_array('*', $abilities)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the subscription associated with the provided user.
     *
     * This method checks for an existing subscription and, if not found,
     * attempts to retrieve a manual subscription for the user.
     *
     * @param User $user The user for whom the subscription is being retrieved.
     * @return Subscription|null The user's subscription or null if none exists.
     */
    public static function getSubscription(User $user): ?Subscription
    {
        return $user->subscription('default') ?? $user->subscription('manual');
    }

    /**
     * Checks if a user's subscription is active.
     *
     * Verifies whether the user is subscribed or has special permissions, such as being a super admin, a technician or an integration user.
     * Also checks if the user's subscription is in the grace period to determine access validity.
     *
     * @param User $user The user instance to check the subscription status for.
     * @return bool Returns true if the user has an active subscription or appropriate permissions, false otherwise.
     */
    public static function checkSubscription(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isIntegration() || $user->isTechnician()) {
            return true;
        }

        $subscription = self::getSubscription($user);

        if ($user->subscribed()) {
            return true;
        }

        if ($subscription && $subscription->onGracePeriod()) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the sensor limit has been reached for a given user based on their subscription.
     *
     * Determines whether the current user has exceeded the number of sensors allowed
     * under their subscription. Super Admins and Integration users are exempt from this limit.
     *
     * @param User $user The user whose sensor usage is being evaluated.
     * @return bool True if the limit is reached, false otherwise.
     */
    public static function isSensorLimitReached(User $user): bool
    {
        $subscription = self::getSubscription($user);
        if (!$user->isSuperAdmin() && !$user->isIntegration()){
            if($user->sensors->count() >= $subscription->quantity){
               return true;
            }
        }

        return false;
    }

   /**
     * Checks if the cadastral units limit has been reached for a given user based on their subscription.
     *
     * Determines whether the current user has exceeded the number of cadastral units allowed
     * under their subscription plan. Super Admins and Integration users are exempt from this limit.
     *
     * @param User $user The user whose cadastral units usage is being evaluated.
     * @return bool True if the limit is reached, false otherwise.
     */
    public static function isCadastralUnitLimitReached(User $user): bool
    {
        // Skip limit for privileged users
        if ($user->isSuperAdmin() || $user->isIntegration()) {
            return false;
        }

        $subscription = self::getSubscription($user);

        // If no active subscription, treat as limit reached
        if (!$subscription) {
            return true;
        }

        $plan = Plan::hasPrice($subscription->stripe_price)->first();
        // If no active plan, treat as limit reached
        if (!$plan) {
            return true;
        }

        // If plan doesn't define a limit (null or 0), ignore the check
        if (!$plan->cadastral_units_number || $plan->cadastral_units_number == 0) {
            return false;
        }

        // If user reached or exceeded the plan limit
        if ($user->cadastralUnits?->count() >= $plan->cadastral_units_number) {
            return true;
        }

        return false;
    }

     /**
     * Checks if the cadastral groups limit has been reached for a given user based on their subscription.
     *
     * Determines whether the current user has exceeded the number of cadastral groups allowed
     * under their subscription plan. Super Admins and Integration users are exempt from this limit.
     *
     * @param User $user The user whose cadastral groups usage is being evaluated.
     * @return bool True if the limit is reached, false otherwise.
     */
    public static function isCadastralGroupsLimitReached(User $user): bool
    {
        // Skip limit for privileged users
        if ($user->isSuperAdmin() || $user->isIntegration()) {
            return false;
        }

        $subscription = self::getSubscription($user);

        // If no active subscription, treat as limit reached
        if (!$subscription) {
            return true;
        }

        $plan = Plan::active()->hasPrice($subscription->stripe_price)->first();
        // If no active plan, treat as limit reached
        if (!$plan) {
            return true;
        }

        // If plan doesn't define a limit (null or 0), ignore the check
        if (!$plan->field_groups_number || $plan->field_groups_number == 0 ) {
            return false;
        }

        // If user reached or exceeded the plan limit
        if ($user->cadastralGroup?->count() >= $plan->field_groups_number) {
            return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @param float $incomingArea The area to be added (sqm)
     * @param int|null $excludeGroupId Optional cadastral_group id to exclude from current total
     * @return array
     */
    public static function isCadastralAreaLimitExceeded(User $user, $incomingArea, ?int $excludeGroupId = null): array
    {
        Log::channel('planLimits')->info("Checking cadastral area limit for user ID {$user->id} with incoming area: $incomingArea sqm");

        // Skip limit for privileged users
        if ($user->isSuperAdmin() || $user->isIntegration()) {
            Log::channel('planLimits')->info('User is super admin or integration, skipping cadastral area limit check.');
            return ['exceeded' => false];
        }

        $subscription = self::getSubscription($user);
        if (!$subscription) {
            Log::channel('planLimits')->info('No active subscription found');
            return [
                'exceeded' => true,
                'reason' => 'no_active_subscription'
            ];
        }

        $plan = Plan::active()->hasPrice($subscription->stripe_price)->first();
        if (!$plan) {
            Log::channel('planLimits')->info('No active plan found for the subscription');
            return [
                'exceeded' => true,
                'reason' => 'no_active_plan'
            ];
        }

        // If plan has no max area or 0, ignore the check (unlimited)
        $maxArea = (float) ($plan->field_groups_max_area ? $plan->field_groups_max_area * 10000 : 0);
        Log::channel('planLimits')->info("Plan maximum cadastral area: $maxArea");
        if ($maxArea <= 0) {
            Log::channel('planLimits')->info('Plan has unlimited cadastral area');
            return ['exceeded' => false];
        }

        $currentArea = self::getTotalCadastralGroupsArea($user);

        if ($excludeGroupId) {
            try {
                $groupArea = (float) CadastralGroup::where('id', $excludeGroupId)->value('total_area') ?? 0.0;
                $currentArea = max(0.0, $currentArea - $groupArea);
            } catch (\Throwable $e) {
                Log::channel('planLimits')->error('Error excluding cadastral group area: ' . $e->getMessage());
            }
        }

        $projected = $currentArea + $incomingArea;
        $exceeded = $projected >= $maxArea;

        Log::channel('planLimits')->info("Current cadastral area: $currentArea sqm, Incoming area: $incomingArea sqm, Max allowed area: $maxArea sqm, Projected cadastral area: $projected sqm. Limit exceeded: " . ($exceeded ? 'Yes' : 'No'));

        return [
            'exceeded' => $exceeded,
            'reason' => $exceeded ? 'area_limit_exceeded' : null,
            'current_area' => $currentArea,
            'incoming_area' => $incomingArea,
            'max_area' => $maxArea,
            'projected_area' => $projected
        ];
    }

    /**
     * Sum all cadastral group areas for a user.
     * Assumes a numeric 'area' column on cadastral groups.
     */
    public static function getTotalCadastralGroupsArea(User $user): float
    {
        // Sum as float (DB decimal will cast fine)
        return (float) $user->cadastralGroup()->sum('total_area');
    }

    /**
     * Remaining area allowance based on plan.
     * Returns null when unlimited; 0 when blocked/no plan/subscription.
     */
    public static function getRemainingCadastralArea(User $user): ?float
    {
        if ($user->isSuperAdmin() || $user->isIntegration()) {
            return null; // effectively unlimited
        }

        $subscription = self::getSubscription($user);
        if (!$subscription) {
            return 0.0;
        }

        $plan = Plan::active()->hasPrice($subscription->stripe_price)->first();
        if (!$plan) {
            return 0.0;
        }

        $maxArea = (float) ($plan->field_groups_max_area ?? 0);
        if ($maxArea <= 0) {
            return null; // unlimited
        }

        $currentArea = self::getTotalCadastralGroupsArea($user);
        return max(0.0, $maxArea - $currentArea);
    }

}
