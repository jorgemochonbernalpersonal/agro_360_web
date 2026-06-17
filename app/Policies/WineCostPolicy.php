<?php

namespace App\Policies;

use App\Models\WineCost;
use App\Models\User;

class WineCostPolicy
{
    public function update(User $user, WineCost $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, WineCost $model): bool
    {
        return $this->update($user, $model);
    }
}
