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
        return User::factory()->create([
            'role'              => 'producer',
            'email_verified_at' => now(),
        ]);
    }

    protected function makeOtherProducer(): User
    {
        return User::factory()->create([
            'role'              => 'producer',
            'email_verified_at' => now(),
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
