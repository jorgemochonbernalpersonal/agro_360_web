<?php

namespace App\Policies;

use App\Models\CellarOperation;
use App\Models\User;

class CellarOperationPolicy
{
    public function update(User $user, CellarOperation $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, CellarOperation $model): bool
    {
        return $this->update($user, $model);
    }
}
