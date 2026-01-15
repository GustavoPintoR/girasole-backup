<?php

namespace App\Policies;

use App\Models\TermsAndConditions;
use App\Models\User;

class TermsAndConditionsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read_terms_and_conditions');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TermsAndConditions $termsAndConditions): bool
    {
        return $user->can('read_terms_and_conditions');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_terms_and_conditions');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TermsAndConditions $termsAndConditions): bool
    {
        return $user->can('update_terms_and_conditions');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TermsAndConditions $termsAndConditions): bool
    {
        return $user->can('delete_terms_and_conditions');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TermsAndConditions $termsAndConditions): bool
    {
        return $user->can('restore_terms_and_conditions');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TermsAndConditions $termsAndConditions): bool
    {
        return $user->can('forceDelete_terms_and_conditions');
    }
}
