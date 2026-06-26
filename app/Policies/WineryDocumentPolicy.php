<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WineryDocument;

class WineryDocumentPolicy
{
    public function update(User $user, WineryDocument $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, WineryDocument $model): bool
    {
        return $this->update($user, $model);
    }
}
