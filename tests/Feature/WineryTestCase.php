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
            'role'              => 'winery',
            'email_verified_at' => now(),
        ]);
    }

    protected function makeOtherWinery(): User
    {
        return User::factory()->create([
            'role'              => 'winery',
            'email_verified_at' => now(),
        ]);
    }
}
