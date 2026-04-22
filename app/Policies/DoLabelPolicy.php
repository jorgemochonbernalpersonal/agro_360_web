<?php

namespace App\Policies;

use App\Models\DoLabel;
use App\Models\User;

class DoLabelPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function view(User $user, DoLabel $label): bool
    {
        return $user->role === 'admin' || $label->supervisor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function update(User $user, DoLabel $label): bool
    {
        return $user->role === 'admin' || $label->supervisor_id === $user->id;
    }

    public function delete(User $user, DoLabel $label): bool
    {
        return $this->update($user, $label);
    }
}
