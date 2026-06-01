<?php

namespace App\Policies;

use App\Models\Container;
use App\Models\User;

class ContainerPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery', 'producer']);
    }

    public function view(User $user, Container $container): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $container->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery', 'producer']);
    }

    public function update(User $user, Container $container): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $container->user_id === $user->id;
    }

    public function delete(User $user, Container $container): bool
    {
        return $this->update($user, $container);
    }
}
