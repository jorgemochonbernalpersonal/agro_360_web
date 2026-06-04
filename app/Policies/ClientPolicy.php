<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery', 'viticulturist', 'producer']);
    }

    public function view(User $user, Client $client): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $client->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery', 'viticulturist', 'producer']);
    }

    public function update(User $user, Client $client): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $client->user_id === $user->id;
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->update($user, $client);
    }
}
