<?php

namespace App\Policies;

use App\Models\CadastralUnit;
use App\Models\User;

class CadastralUnitPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read_cadastral_unit');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CadastralUnit $cadastralUnit): bool
    {
        return $user->can('read_cadastral_unit') && $cadastralUnit->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_cadastral_unit');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CadastralUnit $cadastralUnit): bool
    {
        return $user->can('update_cadastral_unit') && $cadastralUnit->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CadastralUnit $cadastralUnit): bool
    {
        return $user->can('delete_cadastral_unit') && $cadastralUnit->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CadastralUnit $cadastralUnit): bool
    {
        return $user->can('restore_cadastral_unit') && $cadastralUnit->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CadastralUnit $cadastralUnit): bool
    {
        return $user->can('forceDelete_cadastral_unit') && $cadastralUnit->user_id === $user->id;
    }
}
