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
}
