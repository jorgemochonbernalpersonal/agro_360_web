<?php

namespace App\Policies;

use App\Models\DoInspection;
use App\Models\User;

class DoInspectionPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function view(User $user, DoInspection $inspection): bool
    {
        return $user->role === 'admin' || $inspection->supervisor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function update(User $user, DoInspection $inspection): bool
    {
        return $user->role === 'admin' || $inspection->supervisor_id === $user->id;
    }

    public function delete(User $user, DoInspection $inspection): bool
    {
        return $this->update($user, $inspection);
    }
}
