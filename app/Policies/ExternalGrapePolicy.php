<?php

namespace App\Policies;

use App\Models\ExternalGrape;
use App\Models\User;

class ExternalGrapePolicy
{
    public function update(User $user, ExternalGrape $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, ExternalGrape $model): bool
    {
        return $this->update($user, $model);
    }
}
