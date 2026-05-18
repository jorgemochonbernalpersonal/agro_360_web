<?php

namespace App\Livewire\Viticulturist;

use App\Livewire\Shared\AbstractEdit as SharedAbstractEdit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class AbstractEdit extends SharedAbstractEdit
{
    // WithOwnershipRules inherited via Shared\AbstractEdit

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

    protected function authorizeOwnership(Model $model): void
    {
        if ($model->viticulturist_id !== Auth::id()) {
            abort(403);
        }
    }
}
