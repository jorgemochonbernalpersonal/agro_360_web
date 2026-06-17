<?php

namespace App\Policies;

use App\Models\Oenologist;
use App\Models\User;

class OenologistPolicy
{
    public function update(User $user, Oenologist $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, Oenologist $model): bool
    {
        return $this->update($user, $model);
    }
}
