<?php

namespace App\Policies;

use App\Models\PlantDisease;
use App\Models\User;

class PlantDiseasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read_plant_disease');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PlantDisease $plantDisease): bool
    {
        return $user->can('read_plant_disease');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_plant_disease');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PlantDisease $plantDisease): bool
    {
        return $user->can('update_plant_disease');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PlantDisease $plantDisease): bool
    {
        return $user->can('delete_plant_disease');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PlantDisease $plantDisease): bool
    {
        return $user->can('restore_plant_disease');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PlantDisease $plantDisease): bool
    {
        return $user->can('forceDelete_plant_disease');
    }
}
