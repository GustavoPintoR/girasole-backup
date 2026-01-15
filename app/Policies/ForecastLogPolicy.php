<?php

namespace App\Policies;

use App\Models\ForecastLog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ForecastLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read_forecast_log');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ForecastLog $forecastLog): bool
    {
        return $user->can('read_forecast_log');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_forecast_log');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ForecastLog $forecastLog): bool
    {
        return $user->can('update_forecast_log');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ForecastLog $forecastLog): bool
    {
       return $user->can('delete_forecast_log');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ForecastLog $forecastLog): bool
    {
       return $user->can('restore_forecast_log');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ForecastLog $forecastLog): bool
    {
        return $user->can('forceDelete_forecast_log');
    }
}
