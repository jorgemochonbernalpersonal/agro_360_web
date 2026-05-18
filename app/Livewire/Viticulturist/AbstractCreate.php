<?php

namespace App\Livewire\Viticulturist;

use App\Livewire\Shared\AbstractCreate as SharedAbstractCreate;
use Illuminate\Support\Facades\Auth;

abstract class AbstractCreate extends SharedAbstractCreate
{
    // WithOwnershipRules inherited via Shared\AbstractCreate

    protected function viticulturistId(): int
    {
        return Auth::id();
    }

    protected function rolePrefix(): string
    {
        return match(Auth::user()?->role) {
            'producer' => 'producer',
            default    => 'viticulturist',
        };
    }
}
