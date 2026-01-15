<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Sensor;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SensorPolicy
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
        return $user->can('read_sensor');
    }

    public function view(User $user, Sensor $sensor): bool
    {
        $isOwner = $sensor->owner_id === $user->id;

        return $user->can('read_sensor') && $isOwner;
    }

    public function create(User $user): bool
    {
        return $user->can('create_sensor');
    }

    public function update(User $user, Sensor $sensor): bool
    {
        // Only the owner can edit (super admin handled in before)
        return $user->can('update_sensor') && $sensor->owner_id === $user->id;
    }

    public function delete(User $user, Sensor $sensor): bool
    {
        return $user->can('delete_sensor') && $sensor->owner_id === $user->id;
    }

    public function restore(User $user, Sensor $sensor): bool
    {
        return $user->can('restore_sensor') && $sensor->owner_id === $user->id;
    }

    public function forceDelete(User $user, Sensor $sensor): bool
    {
        return $user->can('forceDelete_sensor') && $sensor->owner_id === $user->id;
    }
}
