<?php

namespace App\Policies;

use App\Models\WinerySupply;
use App\Models\User;

class WinerySupplyPolicy
{
    public function update(User $user, WinerySupply $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, WinerySupply $model): bool
    {
        return $this->update($user, $model);
    }
}
