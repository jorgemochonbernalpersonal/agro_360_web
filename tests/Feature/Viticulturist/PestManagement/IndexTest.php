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
        // Use unique names not present in seeder data (seeder has 'Araña Roja', 'Filoxera' which
        // appear in risk-period alerts and would break assertDontSee regardless of search filter).
        $this->makePest(['name' => 'ZZZ_Araña de Prueba']);
        $this->makePest(['name' => 'ZZZ_Pulgón de Prueba']);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('search', 'ZZZ_Araña')
            ->assertSee('ZZZ_Araña de Prueba')
            ->assertDontSee('ZZZ_Pulgón de Prueba');
    }

    public function test_search_by_scientific_name(): void
    {
        // Use unique names/scientific names not present in seeder data.
        $this->makePest(['name' => 'ZZZ_Ácaro de Prueba', 'scientific_name' => 'Zzzus testicus']);
        $this->makePest(['name' => 'ZZZ_Trips de Prueba', 'scientific_name' => 'Zzzus differentus']);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('search', 'Zzzus testicus')
            ->assertSee('ZZZ_Ácaro de Prueba')
            ->assertDontSee('ZZZ_Trips de Prueba');
    }

    // ── Type Filter ───────────────────────────────────────────────────────────

    public function test_type_filter_pest_shows_only_pests(): void
    {
        // Use unique names to avoid collisions with seeder data
        $this->makePest(['type' => 'pest', 'name' => 'TestPest_UniqueXYZ']);
        $this->makePest(['type' => 'disease', 'name' => 'TestDisease_UniqueXYZ']);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('typeFilter', 'pest')
            ->assertSee('TestPest_UniqueXYZ')
            ->assertDontSee('TestDisease_UniqueXYZ');
    }

    public function test_type_filter_disease_shows_only_diseases(): void
    {
        // Use unique names to avoid collisions with seeder data
        $this->makePest(['type' => 'pest', 'name' => 'TestPest_UniqueXYZ']);
        $this->makePest(['type' => 'disease', 'name' => 'TestDisease_UniqueXYZ']);

        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('typeFilter', 'disease')
            ->assertSee('TestDisease_UniqueXYZ')
            ->assertDontSee('TestPest_UniqueXYZ');
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
