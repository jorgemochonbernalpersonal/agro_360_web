<?php

namespace App\Livewire\Viticulturist;

use App\Livewire\Concerns\WithViticulturistValidation;
use App\Livewire\Shared\AbstractEdit as SharedAbstractEdit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class AbstractEdit extends SharedAbstractEdit
{
    use WithViticulturistValidation;

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
