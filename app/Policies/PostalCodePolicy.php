<?php

namespace App\Policies;

use App\Models\PostalCode;
use App\Models\User;

class PostalCodePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read_postal_code');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PostalCode $postalCode): bool
    {
        return $user->can('read_postal_code');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_postal_code');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PostalCode $postalCode): bool
    {
        return $user->can('update_postal_code');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PostalCode $postalCode): bool
    {
        return $user->can('delete_postal_code');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PostalCode $postalCode): bool
    {
        return $user->can('restore_postal_code');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PostalCode $postalCode): bool
    {
        return $user->can('forceDelete_postal_code');
    }
}
