<?php

namespace Tests\Feature\Winery\Denomination;

use App\Livewire\Winery\Denomination\Index;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class IndexTest extends WineryTestCase
{
    // ── render ────────────────────────────────────────────────────────────────

    public function test_renders_for_winery(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('winery.denomination.index'))
            ->assertOk();
    }

    public function test_shows_no_do_message_when_no_supervisor(): void
    {
        $winery = $this->makeWinery();

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertSee('Sin Denominación de Origen asignada');
    }

    // ── con supervisor ────────────────────────────────────────────────────────

    public function test_shows_supervisor_name_when_assigned(): void
    {
        $winery     = $this->makeWinery();
        $supervisor = User::factory()->create(['role' => 'supervisor', 'name' => 'DO Rioja']);

        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'assigned_by'   => $supervisor->id,
        ]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertSee('DO Rioja')
            ->assertSee('Adscrita');
    }

    // ── viticultores DO ───────────────────────────────────────────────────────

    public function test_shows_viticulturists_assigned_by_supervisor(): void
    {
        $winery     = $this->makeWinery();
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $vit        = User::factory()->create(['role' => 'viticulturist', 'name' => 'Vit DO Test']);

        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'assigned_by'   => $supervisor->id,
        ]);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $vit->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $supervisor->id,
            'assigned_by'      => $supervisor->id,
        ]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertSee('Vit DO Test');
    }

    public function test_does_not_show_own_viticulturists_in_do_section(): void
    {
        $winery     = $this->makeWinery();
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $ownVit     = User::factory()->create(['role' => 'viticulturist', 'name' => 'Vit Propio']);

        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'assigned_by'   => $supervisor->id,
        ]);

        // Viticultor source=own (no del supervisor)
        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $ownVit->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertViewHas('doViticulturists', fn ($list) => $list->isEmpty());
    }

    // ── aislamiento ──────────────────────────────────────────────────────────

    public function test_winery_only_sees_their_own_supervisor(): void
    {
        $winery      = $this->makeWinery();
        $otherWinery = $this->makeOtherWinery();
        $supervisor  = User::factory()->create(['role' => 'supervisor', 'name' => 'DO Ajena']);

        // Solo otherWinery está adscrita a esta DO
        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $otherWinery->id,
            'assigned_by'   => $supervisor->id,
        ]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertDontSee('DO Ajena')
            ->assertSee('Sin Denominación de Origen asignada');
    }
}
