<?php

namespace Tests\Browser\Auth;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * Flujo crítico: Control de acceso por rol.
 *
 * Reglas del middleware CheckRole:
 * - guest:          → redirect /login
 * - viticulturist:  puede /viticulturist/*, recibe 403 en /winery/* y /producer/*
 * - winery:         puede /winery/*, recibe 403 en /viticulturist/* y /producer/*
 * - producer:       puede /producer/*, recibe 403 en /winery/* y /viticulturist/*
 *                   (sus rutas están bajo /producer/ con middleware role:winery,producer y role:viticulturist,producer)
 */
class RoleAccessTest extends DuskTestCase
{
    // ─── Guest ───────────────────────────────────────────────────────────────

    #[Test]
    public function guest_is_redirected_to_login_from_protected_routes(): void
    {
        $this->browse(function (Browser $browser) {
            foreach (['/winery/dashboard', '/viticulturist/dashboard', '/producer/dashboard'] as $url) {
                $browser->visit($url)
                    ->waitFor('body', 5)
                    ->assertPathIs('/login')
                    ->screenshot('guest-blocked-'.str_replace('/', '-', trim($url, '/')));
            }
        });
    }

    // ─── Viticulturist ───────────────────────────────────────────────────────

    #[Test]
    public function viticulturist_can_access_own_area(): void
    {
        $user = $this->createViticulturist();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/viticulturist/dashboard')
                ->waitFor('body', 5)
                ->assertPathBeginsWith('/viticulturist/')
                ->screenshot('vitic-own-dashboard');
        });
    }

    #[Test]
    public function viticulturist_gets_403_on_winery_routes(): void
    {
        $user = $this->createViticulturist();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/winery/dashboard')
                ->waitFor('body', 5)
                // abort(403) → la página muestra error 403, no el contenido de bodega
                ->assertSee('403')
                ->screenshot('vitic-403-winery');
        });
    }

    #[Test]
    public function viticulturist_gets_403_on_producer_routes(): void
    {
        $user = $this->createViticulturist();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/dashboard')
                ->waitFor('body', 5)
                ->assertSee('403')
                ->screenshot('vitic-403-producer');
        });
    }

    // ─── Winery ──────────────────────────────────────────────────────────────

    #[Test]
    public function winery_can_access_own_area(): void
    {
        $user = $this->createWinery();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/winery/dashboard')
                ->waitFor('body', 5)
                ->assertPathBeginsWith('/winery/')
                ->assertDontSee('403')
                ->screenshot('winery-own-dashboard');
        });
    }

    #[Test]
    public function winery_gets_403_on_viticulturist_routes(): void
    {
        $user = $this->createWinery();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/viticulturist/dashboard')
                ->waitFor('body', 5)
                ->assertSee('403')
                ->screenshot('winery-403-vitic');
        });
    }

    #[Test]
    public function winery_gets_403_on_producer_routes(): void
    {
        $user = $this->createWinery();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/dashboard')
                ->waitFor('body', 5)
                ->assertSee('403')
                ->screenshot('winery-403-producer');
        });
    }

    // ─── Producer (acceso bajo /producer/*) ──────────────────────────────────

    #[Test]
    public function producer_can_access_producer_area(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/dashboard')
                ->waitFor('body', 5)
                ->assertPathBeginsWith('/producer/')
                ->assertDontSee('403')
                ->screenshot('producer-own-dashboard');
        });
    }

    #[Test]
    public function producer_can_access_wines_under_producer_prefix(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/wines')
                ->waitFor('body', 5)
                ->assertPathBeginsWith('/producer/')
                ->assertDontSee('403')
                ->screenshot('producer-wines-access');
        });
    }

    #[Test]
    public function producer_can_access_plots_under_producer_prefix(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/producer/plots')
                ->waitFor('body', 5)
                ->assertPathBeginsWith('/producer/')
                ->assertDontSee('403')
                ->screenshot('producer-plots-access');
        });
    }

    #[Test]
    public function producer_gets_403_on_pure_winery_prefix(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            // /winery/* tiene middleware role:winery — producer recibe 403
            $browser->loginAs($user)
                ->visit('/winery/dashboard')
                ->waitFor('body', 5)
                ->assertSee('403')
                ->screenshot('producer-403-winery-prefix');
        });
    }

    #[Test]
    public function producer_gets_403_on_pure_viticulturist_prefix(): void
    {
        $user = $this->createProducer();

        $this->browse(function (Browser $browser) use ($user) {
            // /viticulturist/* tiene middleware role:viticulturist — producer recibe 403
            $browser->loginAs($user)
                ->visit('/viticulturist/dashboard')
                ->waitFor('body', 5)
                ->assertSee('403')
                ->screenshot('producer-403-viticulturist-prefix');
        });
    }
}
