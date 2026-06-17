<?php

namespace App\Policies;

use App\Models\SanitaryRegistration;
use App\Models\User;

class SanitaryRegistrationPolicy
{
    public function update(User $user, SanitaryRegistration $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, SanitaryRegistration $model): bool
    {
        return $this->update($user, $model);
    }
}
