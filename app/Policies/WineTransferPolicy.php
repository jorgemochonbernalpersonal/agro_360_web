<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WineTransfer;

class WineTransferPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery']);
    }

    public function update(User $user, WineTransfer $transfer): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $transfer->wine?->user_id === $user->id;
    }

    public function delete(User $user, WineTransfer $transfer): bool
    {
        return $this->update($user, $transfer);
    }
}
