<?php

namespace Tests\Feature\Viticulturist\PestManagement;

use App\Livewire\Viticulturist\PestManagement\Index;
use App\Models\Pest;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    // ── Helpers ────────────────────────────────────────────────────────────────

    private function makePest(array $overrides = []): Pest
    {
        return Pest::create(array_merge([
            'type'   => 'pest',
            'name'   => 'Test Plaga',
            'active' => true,
        ], $overrides));
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function test_renders_index(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertStatus(200);
    }

    public function test_shows_existing_pests(): void
    {
        $this->makePest(['name' => 'Polilla del Racimo']);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Polilla del Racimo');
    }

    // ── Search ────────────────────────────────────────────────────────────────

    public function test_search_by_name(): void
    {
        $this->makePest(['name' => 'Araña Roja']);
        $this->makePest(['name' => 'Filoxera']);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('search', 'Araña')
            ->assertSee('Araña Roja')
            ->assertDontSee('Filoxera');
    }

    public function test_search_by_scientific_name(): void
    {
        $this->makePest(['name' => 'Araña Roja', 'scientific_name' => 'Tetranychus urticae']);
        $this->makePest(['name' => 'Filoxera', 'scientific_name' => 'Daktulosphaira vitifoliae']);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('search', 'Tetranychus')
            ->assertSee('Araña Roja')
            ->assertDontSee('Filoxera');
    }

    // ── Type Filter ───────────────────────────────────────────────────────────

    public function test_type_filter_pest_shows_only_pests(): void
    {
        $this->makePest(['type' => 'pest', 'name' => 'Araña Roja']);
        $this->makePest(['type' => 'disease', 'name' => 'Mildiu']);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('typeFilter', 'pest')
            ->assertSee('Araña Roja')
            ->assertDontSee('Mildiu');
    }

    public function test_type_filter_disease_shows_only_diseases(): void
    {
        $this->makePest(['type' => 'pest', 'name' => 'Araña Roja']);
        $this->makePest(['type' => 'disease', 'name' => 'Mildiu']);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('typeFilter', 'disease')
            ->assertSee('Mildiu')
            ->assertDontSee('Araña Roja');
    }

    // ── Risk Filter ───────────────────────────────────────────────────────────

    public function test_show_only_risk_filters_by_current_month(): void
    {
        $currentMonth = now()->month;

        $this->makePest(['name' => 'En Riesgo', 'risk_months' => [$currentMonth]]);
        $this->makePest(['name' => 'Sin Riesgo', 'risk_months' => []]);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('showOnlyRisk', true)
            ->assertSee('En Riesgo')
            ->assertDontSee('Sin Riesgo');
    }

    // ── Clear Filters ─────────────────────────────────────────────────────────

    public function test_clear_filters_resets_all(): void
    {
        $this->makePest(['name' => 'Araña Roja']);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('search', 'algo')
            ->set('typeFilter', 'pest')
            ->set('showOnlyRisk', true)
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('typeFilter', 'all')
            ->assertSet('showOnlyRisk', false);
    }

    // ── Risk Alert ────────────────────────────────────────────────────────────

    public function test_shows_risk_alert_when_pests_in_risk(): void
    {
        $currentMonth = now()->month;
        $this->makePest(['name' => 'Plaga Peligrosa', 'risk_months' => [$currentMonth]]);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Plaga Peligrosa');
    }

    // ── Inactive pests not shown ──────────────────────────────────────────────

    public function test_inactive_pests_not_shown(): void
    {
        $this->makePest(['name' => 'Plaga Activa', 'active' => true]);
        $this->makePest(['name' => 'Plaga Inactiva', 'active' => false]);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Plaga Activa')
            ->assertDontSee('Plaga Inactiva');
    }
}
