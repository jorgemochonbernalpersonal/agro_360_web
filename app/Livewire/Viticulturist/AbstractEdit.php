<?php

namespace App\Livewire\Viticulturist;

use App\Livewire\Shared\AbstractEdit as SharedAbstractEdit;
use Illuminate\Support\Facades\Auth;

abstract class AbstractEdit extends SharedAbstractEdit
{
    // WithOwnershipRules + authorizeOwnership() inherited via Shared\AbstractEdit

    protected function viticulturistId(): int
    {
        return Auth::id();
    }

    protected function rolePrefix(): string
    {
        return match (Auth::user()?->role) {
            'producer' => 'producer',
            default => 'viticulturist',
        };
    }

    protected function ownerColumn(): string
    {
        return 'viticulturist_id';
    }
}
