<?php

namespace App\Policies;

use App\Models\ContainerRoom;
use App\Models\User;

class ContainerRoomPolicy
{
    public function update(User $user, ContainerRoom $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, ContainerRoom $model): bool
    {
        return $this->update($user, $model);
    }
}
