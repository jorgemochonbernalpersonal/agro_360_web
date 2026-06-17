<?php

namespace App\Policies;

use App\Models\BottlingAuthorization;
use App\Models\User;

class BottlingAuthorizationPolicy
{
    public function update(User $user, BottlingAuthorization $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, BottlingAuthorization $model): bool
    {
        return $this->update($user, $model);
    }
}
