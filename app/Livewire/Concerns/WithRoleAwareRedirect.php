<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Provides role-aware Livewire redirect helpers.
 *
 * Winery and viticulturist components are reused by the producer role under the producer.* route
 * prefix. Using plain redirect()->route('winery.*') or redirect()->route('viticulturist.*')
 * breaks wire:navigate and lands the producer on /winery/... or /viticulturist/... URLs,
 * desynchronising the sidebar state.
 *
 * Usage:
 *   return $this->roleRedirect('grape-reception.index');           // for winery/producer
 *   return $this->viticulturistRoleRedirect('digital-notebook.treatment.index'); // for viticulturist/producer
 */
trait WithRoleAwareRedirect
{
    /**
     * Redirect using the correct route prefix for the authenticated user's role (winery/producer)
     * and Livewire's wire:navigate client-side navigation.
     *
     * @param string $routeSuffix Route name without the role prefix, e.g. 'grape-reception.index'
     * @param mixed  $parameters  Route parameters (array or model)
     */
    protected function roleRedirect(string $routeSuffix, mixed $parameters = []): mixed
    {
        $prefix = Auth::user()->isProducer() ? 'producer' : 'winery';

        return $this->redirect(
            route("{$prefix}.{$routeSuffix}", $parameters),
            navigate: true,
        );
    }

    /**
     * Redirect using the correct route prefix for the authenticated user's role (viticulturist/producer).
     *
     * @param string $routeSuffix Route name without the role prefix, e.g. 'digital-notebook.treatment.index'
     * @param mixed  $parameters  Route parameters (array or model)
     */
    protected function viticulturistRoleRedirect(string $routeSuffix, mixed $parameters = []): mixed
    {
        $prefix = Auth::user()->isProducer() ? 'producer' : 'viticulturist';

        return $this->redirect(
            route("{$prefix}.{$routeSuffix}", $parameters),
            navigate: true,
        );
    }
}
