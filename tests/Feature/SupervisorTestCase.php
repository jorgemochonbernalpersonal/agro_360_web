<?php

namespace Tests\Feature;

use App\Models\SupervisorWinery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class SupervisorTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Supervisor con bodega adscrita ya vinculada.
     * Devuelve [$supervisor, $winery].
     */
    protected function makeSupervisorWithWinery(): array
    {
        $supervisor = User::factory()->create([
            'role'              => 'supervisor',
            'email_verified_at' => now(),
        ]);

        $winery = User::factory()->create([
            'role'              => 'winery',
            'email_verified_at' => now(),
            'can_login'         => true,
        ]);

        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'assigned_by'   => $supervisor->id,
        ]);

        return [$supervisor, $winery];
    }

    protected function makeSupervisor(): User
    {
        return User::factory()->create([
            'role'              => 'supervisor',
            'email_verified_at' => now(),
        ]);
    }

    protected function makeWinery(): User
    {
        return User::factory()->create([
            'role'              => 'winery',
            'email_verified_at' => now(),
            'can_login'         => true,
        ]);
    }
}
