<?php

namespace App\Livewire\Winery;

use App\Livewire\Shared\AbstractEdit as SharedAbstractEdit;
use Illuminate\Support\Facades\Auth;

abstract class AbstractEdit extends SharedAbstractEdit
{
    /**
     * Returns the authenticated winery's user ID.
     */
    protected function wineryId(): int
    {
        return Auth::id();
    }
}
