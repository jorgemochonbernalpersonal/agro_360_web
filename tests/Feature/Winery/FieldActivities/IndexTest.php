<?php

namespace Tests\Feature\Winery\FieldActivities;

use App\Livewire\Winery\FieldActivities\Index;
use App\Models\AgriculturalActivity;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class IndexTest extends WineryTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeViticulturist(): User
    {
        return User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
            'can_login'         => true,
        ]);
    }

    private function linkWithAccess(User $winery, User $viticulturist, bool $cuadernoAccess = true): WineryViticulturist
    {
        return WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
            'cuaderno_access'  => $cuadernoAccess,
        ]);
    }

    // ── access ────────────────────────────────────────────────────────────────

    public function test_winery_can_access_field_activities_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('winery.field-activities.index'))
            ->assertOk();
    }

    public function test_component_renders(): void
    {
        $winery = $this->makeWinery();

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertOk();
    }

    // ── lógica de consentimiento cuaderno ─────────────────────────────────────

    public function test_shows_activities_when_cuaderno_access_granted(): void
    {
        $winery        = $this->makeWinery();
        $viticulturist = $this->makeViticulturist();
        $this->linkWithAccess($winery, $viticulturist, cuadernoAccess: true);

        AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->create(['activity_type' => 'irrigation', 'activity_date' => now()]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('stats', fn($s) => $s['total'] === 1);
    }

    public function test_hides_activities_when_cuaderno_access_not_granted(): void
    {
        $winery        = $this->makeWinery();
        $viticulturist = $this->makeViticulturist();
        $this->linkWithAccess($winery, $viticulturist, cuadernoAccess: false);

        AgriculturalActivity::factory()
            ->forViticulturist($viticulturist)
            ->create(['activity_type' => 'irrigation', 'activity_date' => now()]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('stats', fn($s) => $s['total'] === 0);
    }

    public function test_does_not_show_unrelated_viticulturist_activities(): void
    {
        $winery    = $this->makeWinery();
        $outsideVit = $this->makeViticulturist();
        // No link between winery and outsideVit

        AgriculturalActivity::factory()
            ->forViticulturist($outsideVit)
            ->create(['activity_type' => 'fertilization', 'activity_date' => now()]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('stats', fn($s) => $s['total'] === 0);
    }

    // ── warning de viticulturists sin acceso ──────────────────────────────────

    public function test_without_cuaderno_access_list_is_populated(): void
    {
        $winery        = $this->makeWinery();
        $viticulturist = $this->makeViticulturist();
        $this->linkWithAccess($winery, $viticulturist, cuadernoAccess: false);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('withoutCuadernoAccess', fn($list) =>
                $list->contains('id', $viticulturist->id)
            );
    }

    public function test_without_cuaderno_access_list_excludes_ghost_viticulturists(): void
    {
        $winery = $this->makeWinery();
        $ghost  = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => false,
        ]);
        $this->linkWithAccess($winery, $ghost, cuadernoAccess: false);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('withoutCuadernoAccess', fn($list) =>
                $list->doesntContain('id', $ghost->id)
            );
    }

    // ── stats ─────────────────────────────────────────────────────────────────

    public function test_stats_count_harvest_activities(): void
    {
        $winery        = $this->makeWinery();
        $viticulturist = $this->makeViticulturist();
        $this->linkWithAccess($winery, $viticulturist, cuadernoAccess: true);

        AgriculturalActivity::factory()->forViticulturist($viticulturist)->create(['activity_type' => 'harvest',      'activity_date' => now()]);
        AgriculturalActivity::factory()->forViticulturist($viticulturist)->create(['activity_type' => 'phytosanitary','activity_date' => now()]);
        AgriculturalActivity::factory()->forViticulturist($viticulturist)->create(['activity_type' => 'irrigation',   'activity_date' => now()]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('stats', fn($s) =>
                $s['total'] === 3 && $s['harvest'] === 1 && $s['phyto'] === 1
            );
    }

    // ── filtro por viticulturist ──────────────────────────────────────────────

    public function test_filter_by_viticulturist(): void
    {
        $winery = $this->makeWinery();
        $vit1   = $this->makeViticulturist();
        $vit2   = $this->makeViticulturist();
        $this->linkWithAccess($winery, $vit1, cuadernoAccess: true);
        $this->linkWithAccess($winery, $vit2, cuadernoAccess: true);

        AgriculturalActivity::factory()->forViticulturist($vit1)->create(['activity_date' => now()]);
        AgriculturalActivity::factory()->forViticulturist($vit2)->create(['activity_date' => now()]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->set('viticulturistFilter', (string) $vit1->id)
            ->assertViewHas('stats', fn($s) => $s['total'] === 1);
    }
}
