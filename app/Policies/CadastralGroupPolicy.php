<?php

namespace App\Policies;

use App\Models\CadastralGroup;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CadastralGroupPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read_cadastral_group');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CadastralGroup $cadastralGroup): bool
    {
        return $user->can('read_cadastral_group') && $cadastralGroup->canBeMangedBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_cadastral_group');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CadastralGroup $cadastralGroup): bool
    {
        return $user->can('update_cadastral_group') && $cadastralGroup->canBeMangedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CadastralGroup $cadastralGroup): bool
    {
       return $user->can('delete_cadastral_group') && $cadastralGroup->canBeMangedBy($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CadastralGroup $cadastralGroup): bool
    {
       return $user->can('restore_cadastral_group') && $cadastralGroup->canBeMangedBy($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CadastralGroup $cadastralGroup): bool
    {
        return $user->can('forceDelete_cadastral_group') && $cadastralGroup->canBeMangedBy($user);
    }
}
