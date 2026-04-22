<?php

namespace App\Policies;

use App\Models\DoQualification;
use App\Models\User;

class DoQualificationPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function view(User $user, DoQualification $qualification): bool
    {
        return $user->role === 'admin' || $qualification->supervisor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }

    public function update(User $user, DoQualification $qualification): bool
    {
        return $user->role === 'admin' || $qualification->supervisor_id === $user->id;
    }

    public function delete(User $user, DoQualification $qualification): bool
    {
        return $this->update($user, $qualification);
    }
}
