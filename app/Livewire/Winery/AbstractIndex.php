<?php

namespace App\Livewire\Winery;

use App\Livewire\Shared\AbstractIndex as SharedAbstractIndex;
use Illuminate\Support\Facades\Auth;

abstract class AbstractIndex extends SharedAbstractIndex
{
    /**
     * Returns the authenticated winery's user ID.
     */
    protected function wineryId(): int
    {
        return Auth::id();
    }
}
