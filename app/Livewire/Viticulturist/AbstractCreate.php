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
        return match (Auth::user()?->role) {
            'producer' => 'producer',
            default => 'viticulturist',
        };
    }

    protected function ownerColumn(): string
    {
        return 'viticulturist_id';
    }

    protected function resolveIndexRoute(): string
    {
        $suffix = $this->indexRoute();
        $prefix = $this->rolePrefix();
        $candidate = "{$prefix}.{$suffix}";

        return \Illuminate\Support\Facades\Route::has($candidate) ? $candidate : $suffix;
    }
}
