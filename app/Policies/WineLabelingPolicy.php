<?php

namespace App\Policies;

use App\Models\WineLabeling;
use App\Models\User;

class WineLabelingPolicy
{
    public function update(User $user, WineLabeling $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, WineLabeling $model): bool
    {
        return $this->update($user, $model);
    }
}
