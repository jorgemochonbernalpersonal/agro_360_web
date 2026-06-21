<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WineBottling;

class WineBottlingPolicy
{
    public function update(User $user, WineBottling $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, WineBottling $model): bool
    {
        return $this->update($user, $model);
    }
}
