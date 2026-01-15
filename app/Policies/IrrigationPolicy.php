<?php

namespace App\Policies;

use App\Models\Irrigation;
use App\Models\User;

class IrrigationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read_irrigation');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Irrigation $irrigation): bool
    {
        return $user->can('read_irrigation');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_irrigation');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Irrigation $irrigation): bool
    {
        return $user->can('update_irrigation');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Irrigation $irrigation): bool
    {
        return $user->can('delete_irrigation');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Irrigation $irrigation): bool
    {
        return $user->can('restore_irrigation');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Irrigation $irrigation): bool
    {
        return $user->can('forceDelete_irrigation');
    }
}
