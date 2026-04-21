<?php

namespace Tests\Feature\Supervisor\Oversight;

use App\Livewire\Supervisor\Oversight\Growers\Index;
use App\Models\PlotPlanting;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class GrowersIndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_oversight_growers_index(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.growers.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_oversight_growers_index(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.oversight.growers.index'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_oversight_growers_index(): void
    {
        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.oversight.growers.index'))
            ->assertForbidden();
    }

    // ── data ──────────────────────────────────────────────────────────────────

    public function test_oversight_growers_index_shows_viticulturist_assigned_via_supervisor(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
            'name'              => 'Viticultor Supervisado',
        ]);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $supervisor->id,
            'assigned_by'      => $supervisor->id,
        ]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.growers.index'))
            ->assertSee('Viticultor Supervisado');
    }

    public function test_oversight_growers_index_does_not_show_viticulturist_from_other_supervisor(): void
    {
        [$supervisor, $winery]         = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
            'name'              => 'Viticultor Ajeno',
        ]);

        WineryViticulturist::create([
            'winery_id'        => $otherWinery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $otherSupervisor->id,
            'assigned_by'      => $otherSupervisor->id,
        ]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.oversight.growers.index'))
            ->assertDontSee('Viticultor Ajeno');
    }

    public function test_oversight_growers_livewire_component_renders(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Oversight\Growers\Index::class)
            ->assertOk();
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeViticulturistForOversight(User $supervisor, User $winery, array $userAttrs = []): User
    {
        $vit = User::factory()->create(array_merge([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ], $userAttrs));

        WineryViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'winery_id'        => $winery->id,
            'viticulturist_id' => $vit->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'assigned_by'      => $supervisor->id,
        ]);

        return $vit;
    }

    // ── search ────────────────────────────────────────────────────────────────

    public function test_search_by_name_filters_list(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $visible = $this->makeViticulturistForOversight($supervisor, $winery, ['name' => 'María Viñedo']);
        $hidden  = $this->makeViticulturistForOversight($supervisor, $winery, ['name' => 'Pedro Llano']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('search', 'viñedo')
            ->assertViewHas('growers', fn ($g) =>
                $g->pluck('id')->contains($visible->id) &&
                !$g->pluck('id')->contains($hidden->id)
            );
    }

    public function test_search_by_email_filters_list(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $visible = $this->makeViticulturistForOversight($supervisor, $winery, ['email' => 'buscar@viña.es']);
        $hidden  = $this->makeViticulturistForOversight($supervisor, $winery, ['email' => 'otro@dominio.es']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('search', 'buscar')
            ->assertViewHas('growers', fn ($g) =>
                $g->pluck('id')->contains($visible->id) &&
                !$g->pluck('id')->contains($hidden->id)
            );
    }

    public function test_clear_search_resets_filter(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForOversight($supervisor, $winery, ['name' => 'Ana Bodega']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('search', 'xyz')
            ->assertViewHas('growers', fn ($g) => !$g->pluck('id')->contains($vit->id))
            ->call('clearSearch')
            ->assertSet('search', '')
            ->assertViewHas('growers', fn ($g) => $g->pluck('id')->contains($vit->id));
    }

    // ── totalGrowers ──────────────────────────────────────────────────────────

    public function test_total_growers_reflects_supervisor_source_pool(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $this->makeViticulturistForOversight($supervisor, $winery);
        $this->makeViticulturistForOversight($supervisor, $winery);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalGrowers', 2);
    }

    public function test_source_own_viticulturists_not_included(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $ownVit = User::factory()->create(['role' => 'viticulturist']);
        WineryViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'winery_id'        => $winery->id,
            'viticulturist_id' => $ownVit->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalGrowers', 0);
    }

    // ── plantingCountByVit ────────────────────────────────────────────────────

    public function test_planting_count_by_vit_includes_active_plantings(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForOversight($supervisor, $winery);
        $plot = $this->makePlot($vit);

        PlotPlanting::factory()->create(['plot_id' => $plot->id, 'status' => 'active']);
        PlotPlanting::factory()->create(['plot_id' => $plot->id, 'status' => 'active']);
        PlotPlanting::factory()->create(['plot_id' => $plot->id, 'status' => 'removed']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('plantingCountByVit', fn ($map) =>
                ($map[$vit->id] ?? null) == 2
            );
    }

    // ── lastActivityByVit ─────────────────────────────────────────────────────

    public function test_last_activity_by_vit_returns_most_recent_date(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $vit = $this->makeViticulturistForOversight($supervisor, $winery);

        DB::table('agricultural_activities')->insert([
            ['viticulturist_id' => $vit->id, 'activity_type' => 'pruning',    'activity_date' => '2026-01-10', 'created_at' => now(), 'updated_at' => now()],
            ['viticulturist_id' => $vit->id, 'activity_type' => 'irrigation', 'activity_date' => '2026-03-20', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('lastActivityByVit', fn ($map) =>
                isset($map[$vit->id]) && str_starts_with($map[$vit->id], '2026-03-20')
            );
    }
}
