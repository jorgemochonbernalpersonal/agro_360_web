<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Base class for producer feature tests.
 *
 * Producer is a hybrid role: hasWineryAccess() + hasViticulturistAccess().
 */
abstract class ProducerTestCase extends TestCase
{
    use RefreshDatabase;

    protected function makeProducer(): User
    {
        // Beta vigente = plan activo (hasActiveAccess() = true), para que el
        // producer pueda acceder a los módulos del plan Completo protegidos por
        // require.complete (bodega, facturas, verifactu, vistas integradas…).
        return User::factory()->create([
            'role'                => 'producer',
            'email_verified_at'   => now(),
            'is_beta_user'        => true,
            'beta_ends_at'        => now()->addYear(),
            'beta_access_granted' => true,
        ]);
    }

    protected function makeOtherProducer(): User
    {
        return User::factory()->create([
            'role'                => 'producer',
            'email_verified_at'   => now(),
            'is_beta_user'        => true,
            'beta_ends_at'        => now()->addYear(),
            'beta_access_granted' => true,
        ]);
    }

    protected function makeWinery(): User
    {
        return User::factory()->create([
            'role'              => 'winery',
            'email_verified_at' => now(),
        ]);
    }

    protected function makeViticulturist(): User
    {
        return User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);
    }
}
