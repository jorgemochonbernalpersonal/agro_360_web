<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WineLoss;

class WineLossPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery']);
    }

    public function update(User $user, WineLoss $loss): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $loss->wine?->user_id === $user->id;
    }

    public function delete(User $user, WineLoss $loss): bool
    {
        return $this->update($user, $loss);
    }
}
