<?php

namespace App\Policies;

use App\Models\SensorType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SensorTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read_sensor_type');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SensorType $sensorType): bool
    {
        return $user->can('read_sensor_type');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_sensor_type');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SensorType $sensorType): bool
    {
        return $user->can('update_sensor_type');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SensorType $sensorType): bool
    {
       return $user->can('delete_sensor_type');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SensorType $sensorType): bool
    {
       return $user->can('restore_sensor_type');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SensorType $sensorType): bool
    {
        return $user->can('forceDelete_sensor_type');
    }
}
