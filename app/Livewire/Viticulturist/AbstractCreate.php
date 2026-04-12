<?php

namespace App\Livewire\Viticulturist;

use App\Livewire\Shared\AbstractCreate as SharedAbstractCreate;
use Illuminate\Support\Facades\Auth;

abstract class AbstractCreate extends SharedAbstractCreate
{
    /**
     * Returns the authenticated viticulturist ID.
     */
    protected function viticulturistId(): int
    {
        return Auth::id();
    }

    /**
     * Returns the route prefix for the current user's role (viticulturist or producer).
     */
    protected function rolePrefix(): string
    {
        return match(Auth::user()?->role) {
            'producer' => 'producer',
            default    => 'viticulturist',
        };
    }
}
