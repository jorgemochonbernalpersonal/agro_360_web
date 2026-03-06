<?php

namespace App\Livewire\Winery;

use App\Livewire\Shared\AbstractCreate as SharedAbstractCreate;
use Illuminate\Support\Facades\Auth;

abstract class AbstractCreate extends SharedAbstractCreate
{
    /**
     * Returns the authenticated winery's user ID.
     */
    protected function wineryId(): int
    {
        return Auth::id();
    }
}
