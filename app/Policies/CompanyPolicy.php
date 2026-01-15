<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CompanyPolicy
{
    // Super admin bypass
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('read_company');
    }

    public function view(User $user, Company $company): bool
    {
        // User can view if is owner or is attached via pivot
        // Orphaned companies (null owner_id) can be viewed by members
        $isOwner = $company->owner_id === $user->id;
        $isMember = $company->users()->whereKey($user->id)->exists();

        return $user->can('read_company') && ($isOwner || $isMember);
    }

    public function create(User $user): bool
    {
        return $user->can('create_company');
    }

    public function update(User $user, Company $company): bool
    {
        if ($company->owner_id === null) {
            return false;
        }

        return $user->can('update_company') && $company->owner_id === $user->id;
    }

    public function delete(User $user, Company $company): bool
    {
        if ($company->owner_id === null) {
            return false;
        }

        return $user->can('delete_company') && $company->owner_id === $user->id;
    }

    public function restore(User $user, Company $company): bool
    {
        if ($company->owner_id === null) {
            return false;
        }

        return $user->can('restore_company') && $company->owner_id === $user->id;
    }

    public function forceDelete(User $user, Company $company): bool
    {
        if ($company->owner_id === null) {
            return false;
        }

        return $user->can('forceDelete_company') && $company->owner_id === $user->id;
    }
}
