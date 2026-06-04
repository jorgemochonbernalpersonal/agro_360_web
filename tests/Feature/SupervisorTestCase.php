<?php

namespace Tests\Feature;

use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use App\Models\SupervisorViticulturist;
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
            'role' => 'supervisor',
            'email_verified_at' => now(),
            'can_login' => true,
        ]);

        $winery = User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
            'can_login' => true,
        ]);

        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'assigned_by' => $supervisor->id,
        ]);

        return [$supervisor, $winery];
    }

    protected function makeSupervisor(): User
    {
        return User::factory()->create([
            'role' => 'supervisor',
            'email_verified_at' => now(),
            'can_login' => true,
        ]);
    }

    protected function makeWinery(): User
    {
        return User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
            'can_login' => true,
        ]);
    }

    /**
     * Viticultor adscrito al supervisor dado.
     */
    protected function makeViticulturistForSupervisor(User $supervisor): User
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        SupervisorViticulturist::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by' => $supervisor->id,
        ]);

        return $viticulturist;
    }

    /**
     * Parcela con las FKs de ubicación satisfechas (AC/Province/Municipality).
     */
    protected function makePlot(User $viticulturist, array $attrs = []): Plot
    {
        $ac = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        return Plot::factory()->create(array_merge([
            'viticulturist_id' => $viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id' => $prov->id,
            'municipality_id' => $muni->id,
            'active' => true,
        ], $attrs));
    }
}
