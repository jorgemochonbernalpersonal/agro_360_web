<?php

namespace App\Policies;

use App\Models\WineAnalysis;
use App\Models\User;

class WineAnalysisPolicy
{
    public function update(User $user, WineAnalysis $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, WineAnalysis $model): bool
    {
        return $this->update($user, $model);
    }
}
