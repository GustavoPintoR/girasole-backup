<?php

namespace App\Policies;

use App\Models\Cultivation;
use App\Models\User;

class CultivationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read_cultivation');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cultivation $cultivation): bool
    {
        return $user->can('read_cultivation');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_cultivation');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cultivation $cultivation): bool
    {
        return $user->can('update_cultivation');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cultivation $cultivation): bool
    {
        return $user->can('delete_cultivation');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cultivation $cultivation): bool
    {
        return $user->can('restore_cultivation');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cultivation $cultivation): bool
    {
        return $user->can('forceDelete_cultivation');
    }
}
