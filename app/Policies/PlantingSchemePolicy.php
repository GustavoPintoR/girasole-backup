<?php

namespace App\Policies;

use App\Models\PlantingScheme;
use App\Models\User;

class PlantingSchemePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read_planting_scheme');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PlantingScheme $plantingScheme): bool
    {
        return $user->can('read_planting_scheme');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_planting_scheme');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PlantingScheme $plantingScheme): bool
    {
        return $user->can('update_planting_scheme');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PlantingScheme $plantingScheme): bool
    {
        return $user->can('delete_planting_scheme');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PlantingScheme $plantingScheme): bool
    {
        return $user->can('restore_planting_scheme');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PlantingScheme $plantingScheme): bool
    {
        return $user->can('forceDelete_planting_scheme');
    }
}
