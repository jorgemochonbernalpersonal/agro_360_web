<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wine;

class WinePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery', 'producer']);
    }

    public function view(User $user, Wine $wine): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $wine->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery', 'producer']);
    }

    public function update(User $user, Wine $wine): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $wine->user_id === $user->id;
    }

    public function delete(User $user, Wine $wine): bool
    {
        return $this->update($user, $wine);
    }
}
