<?php

namespace App\Policies;

use App\Models\HarvestDelivery;
use App\Models\User;

class HarvestDeliveryPolicy
{
    public function view(User $user, HarvestDelivery $delivery): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $delivery->viticulturist_id == $user->id;
    }

    public function update(User $user, HarvestDelivery $delivery): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $delivery->viticulturist_id == $user->id;
    }

    public function delete(User $user, HarvestDelivery $delivery): bool
    {
        return $this->update($user, $delivery);
    }
}
