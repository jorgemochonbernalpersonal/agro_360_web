<?php

namespace App\Policies;

use App\Models\EcoCertification;
use App\Models\User;

class EcoCertificationPolicy
{
    public function update(User $user, EcoCertification $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, EcoCertification $model): bool
    {
        return $this->update($user, $model);
    }
}
