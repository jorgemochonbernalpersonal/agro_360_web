<?php

namespace App\Policies;

use App\Models\WineTastingNote;
use App\Models\User;

class WineTastingNotePolicy
{
    public function update(User $user, WineTastingNote $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, WineTastingNote $model): bool
    {
        return $this->update($user, $model);
    }
}
