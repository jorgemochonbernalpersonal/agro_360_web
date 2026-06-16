<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WineFermentationControl;

class WineFermentationControlPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery']);
    }

    public function update(User $user, WineFermentationControl $control): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $control->wine?->user_id === $user->id;
    }

    public function delete(User $user, WineFermentationControl $control): bool
    {
        return $this->update($user, $control);
    }
}
