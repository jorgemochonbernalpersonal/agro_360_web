<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Livewire concern to read/write user preferences (stored as JSON in users.preferences).
 *
 * Usage in any Livewire component:
 *   use WithUserPreferences;
 *
 *   $this->getPreference('view_mode', 'sidebar')
 *   $this->savePreference('view_mode', 'map')
 */
trait WithUserPreferences
{
    public function getPreference(string $key, mixed $default = null): mixed
    {
        return Auth::user()->getPreference($key, $default);
    }

    public function savePreference(string $key, mixed $value): void
    {
        Auth::user()->setPreference($key, $value);
    }
}
