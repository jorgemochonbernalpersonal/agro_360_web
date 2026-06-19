<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Base class for winery feature tests.
 *
 * Provides shared helpers so each test file does not repeat
 * the user-creation boilerplate.
 */
abstract class WineryTestCase extends TestCase
{
    use RefreshDatabase;

    protected function makeWinery(): User
    {
        return User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
            // Acceso activo (beta sin caducidad) → plan 'winery' efectivo, de modo
            // que los módulos premium (product_sales, label_batches, …) gateados por
            // winery.ability quedan habilitados igual que en una bodega real con plan.
            'is_beta_user' => true,
            'beta_ends_at' => null,
        ]);
    }

    protected function makeOtherWinery(): User
    {
        return User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
            'is_beta_user' => true,
            'beta_ends_at' => null,
        ]);
    }
}
