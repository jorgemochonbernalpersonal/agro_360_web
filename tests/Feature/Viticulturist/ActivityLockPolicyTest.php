<?php

namespace Tests\Feature\Viticulturist;

use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\GrapeVariety;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica que las políticas de bloqueo (is_locked) en AgriculturalActivity
 * impiden editar/eliminar actividades bloqueadas.
 */
class ActivityLockPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $viticulturist;

    private User $admin;

    private AgriculturalActivity $unlockedActivity;

    private AgriculturalActivity $lockedActivity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->viticulturist = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);
        $this->admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $ac = AutonomousCommunity::firstOrCreate(['code' => 'TEST'], ['name' => 'Comunidad Test']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Provincia Test', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Municipio Test', 'province_id' => $prov->id]);

        $plot = Plot::factory()->create([
            'viticulturist_id' => $this->viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id' => $prov->id,
            'municipality_id' => $muni->id,
        ]);
        $campaign = Campaign::factory()->active()->create([
            'viticulturist_id' => $this->viticulturist->id,
            'year' => now()->year,
        ]);

        $grapeVariety = GrapeVariety::firstOrCreate(['code' => 'TEMP'], ['name' => 'Tempranillo', 'color' => 'red']);
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted' => 1.0,
            'planting_year' => now()->year - 3,
            'status' => 'active',
        ]);

        $base = [
            'viticulturist_id' => $this->viticulturist->id,
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'observation',
            'activity_date' => now()->toDateString(),
        ];

        $this->unlockedActivity = AgriculturalActivity::factory()->create(array_merge($base, ['is_locked' => false]));
        $this->lockedActivity = AgriculturalActivity::factory()->create(array_merge($base, [
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $this->viticulturist->id,
        ]));
    }

    // ── update policy ──────────────────────────────────────────────────────────

    public function test_viticulturist_can_update_own_unlocked_activity(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('update', $this->unlockedActivity)
        );
    }

    public function test_viticulturist_cannot_update_locked_activity(): void
    {
        $this->assertFalse(
            $this->viticulturist->can('update', $this->lockedActivity)
        );
    }

    // ── delete policy ──────────────────────────────────────────────────────────

    public function test_viticulturist_can_delete_own_unlocked_activity(): void
    {
        $this->assertTrue(
            $this->viticulturist->can('delete', $this->unlockedActivity)
        );
    }

    public function test_viticulturist_cannot_delete_locked_activity(): void
    {
        $this->assertFalse(
            $this->viticulturist->can('delete', $this->lockedActivity)
        );
    }

    // ── unlock (model method) ──────────────────────────────────────────────────

    public function test_viticulturist_cannot_unlock_activity(): void
    {
        $this->actingAs($this->viticulturist);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Solo los administradores pueden desbloquear actividades.');

        $this->lockedActivity->unlock();
    }

    public function test_admin_can_unlock_activity(): void
    {
        $this->actingAs($this->admin);

        $this->lockedActivity->unlock();

        $this->assertFalse($this->lockedActivity->fresh()->is_locked);
        $this->assertNull($this->lockedActivity->fresh()->locked_at);
    }

    // ── lock method ────────────────────────────────────────────────────────────

    public function test_locking_already_locked_activity_is_idempotent(): void
    {
        $this->actingAs($this->viticulturist);
        $lockedAt = $this->lockedActivity->locked_at;

        $this->lockedActivity->lock($this->viticulturist->id);

        // Debe seguir igual — no lanzar error ni cambiar el timestamp
        $this->assertTrue($this->lockedActivity->fresh()->is_locked);
        $this->assertEquals($lockedAt->toDateTimeString(), $this->lockedActivity->fresh()->locked_at->toDateTimeString());
    }
}
