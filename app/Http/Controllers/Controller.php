<?php

namespace App\Http\Controllers;

use App\Helpers\PlanHelper;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

abstract class Controller
{

    /**
     * Checks if a user is eligible to add or manage sensors based on their subscription plan.
     *
     * @param int|string $userId The ID of the user to be checked.
     *
     * @return RedirectResponse|null Returns a redirect response with an error message if the user
     *                               fails one of the eligibility checks, or null if eligible.
     */
    protected function checkSensorEligibility(int|string $userId): RedirectResponse|null
    {
        $user = User::whereId($userId)->first();
        if(!PlanHelper::checkSubscription($user)){
            return redirect()->back()
                ->with('error', __('ui.sensor_user_not_subscribed'));
        }

        if (PlanHelper::isSensorLimitReached($user)){
            return redirect()->back()
                ->with('error', __('ui.maximum_sensors_reached'));
        }

        return null;
    }
}
