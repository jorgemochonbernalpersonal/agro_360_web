<?php

namespace App\Livewire\Viticulturist;

use App\Livewire\Shared\AbstractEdit as SharedAbstractEdit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class AbstractEdit extends SharedAbstractEdit
{
    /**
     * Returns the authenticated viticulturist ID.
     */
    protected function viticulturistId(): int
    {
        return Auth::id();
    }

    /**
     * Aborts with 403 if the model does not belong to the authenticated user.
     */
    protected function authorizeOwnership(Model $model): void
    {
        if ($model->viticulturist_id !== Auth::id()) {
            abort(403);
        }
    }
}
