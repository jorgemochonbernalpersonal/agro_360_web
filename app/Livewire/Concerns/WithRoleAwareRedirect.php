<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Provides role-aware Livewire redirect helpers.
 *
 * Winery components are reused by the producer role under the producer.* route
 * prefix. Using plain redirect()->route('winery.*') breaks wire:navigate and
 * lands the producer on /winery/... URLs, desynchronising the sidebar state.
 *
 * Usage: return $this->roleRedirect('grape-reception.index');
 */
trait WithRoleAwareRedirect
{
    /**
     * Redirect using the correct route prefix for the authenticated user's role
     * and Livewire's wire:navigate client-side navigation.
     *
     * @param  string       $routeSuffix  Route name without the role prefix, e.g. 'grape-reception.index'
     * @param  mixed        $parameters   Route parameters (array or model)
     */
    protected function roleRedirect(string $routeSuffix, mixed $parameters = []): mixed
    {
        $prefix = Auth::user()->isProducer() ? 'producer' : 'winery';

        return $this->redirect(
            route("{$prefix}.{$routeSuffix}", $parameters),
            navigate: true,
        );
    }
}
