<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Base class for viticulturist feature tests.
 *
 * Provides shared helpers so each test file does not repeat
 * the user-creation + winery-link boilerplate.
 */
abstract class ViticulturistTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Creates a viticulturist user with the required WineryViticulturist
     * link (needed for check.beta middleware and component auth).
     */
    protected function makeViticulturist(): User
    {
        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $winery = User::factory()->create(['role' => 'winery']);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        return $viticulturist;
    }

    /**
     * Creates a second viticulturist WITHOUT a winery link.
     * Sufficient for Livewire-level authorization tests that
     * bypass HTTP middleware (role:viticulturist, check.beta).
     */
    protected function makeOtherViticulturist(): User
    {
        return User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);
    }
}
