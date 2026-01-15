<?php

namespace App\Policies;

use App\Models\Cultivar;
use App\Models\User;

class CultivarPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read_cultivar');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cultivar $cultivar): bool
    {
        return $user->can('read_cultivar');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_cultivar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cultivar $cultivar): bool
    {
        return $user->can('update_cultivar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cultivar $cultivar): bool
    {
        return $user->can('delete_cultivar');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cultivar $cultivar): bool
    {
        return $user->can('restore_cultivar');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cultivar $cultivar): bool
    {
        return $user->can('forceDelete_cultivar');
    }
}
