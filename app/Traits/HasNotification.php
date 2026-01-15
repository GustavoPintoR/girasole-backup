<?php

namespace App\Traits;

use App\Models\User;
use App\Notifications\BroadcastMessageNotification;

trait HasNotification
{
    protected function notifyUser(int|string $userId, string $title, string $description, ?int $cadastralUnitId = null): void
    {
        $user = User::find($userId);

        if ($user instanceof User) {
            $context = [
                'title'       => $title,
                'description' => $description,
                'cadastral_unit_id' => $cadastralUnitId,
            ];

            $user->notify(new BroadcastMessageNotification($context, true));
        }
    }
}
