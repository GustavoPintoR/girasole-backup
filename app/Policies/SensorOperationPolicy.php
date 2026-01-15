<?php

namespace App\Policies;

use App\Models\SensorOperation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SensorOperationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read_sensor_operation');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SensorOperation $sensorOperation): bool
    {
        return $user->can('read_sensor_operation');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_sensor_operation');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SensorOperation $sensorOperation): bool
    {
        return $user->can('update_sensor_operation');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SensorOperation $sensorOperation): bool
    {
       return $user->can('delete_sensor_operation');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SensorOperation $sensorOperation): bool
    {
       return $user->can('restore_sensor_operation');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SensorOperation $sensorOperation): bool
    {
        return $user->can('forceDelete_sensor_operation');
    }
}
