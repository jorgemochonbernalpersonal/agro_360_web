<?php

namespace Tests\Feature\Winery\ProductionCosts;

use App\Livewire\Winery\ProductionCosts\Create;
use App\Livewire\Winery\ProductionCosts\Edit;
use App\Models\Wine;
use App\Models\WineCost;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class ProductionCostsTest extends WineryTestCase
{
    private \App\Models\User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    private function makeWine(?int $userId = null): Wine
    {
        return Wine::create([
            'user_id' => $userId ?? $this->winery->id,
            'name' => 'Test Wine',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);
    }

    private function makeCost(Wine $wine): WineCost
    {
        return WineCost::create([
            'wine_id' => $wine->id,
            'user_id' => $wine->user_id,
            'category' => 'analysis',
            'description' => 'Análisis test',
            'amount' => '150.00',
            'cost_date' => now()->toDateString(),
            'created_by' => $wine->user_id,
        ]);
    }

    // --- Index ---

    public function test_index_renders(): void
    {
        $this->get(route('winery.production-costs.index'))->assertOk();
    }

    public function test_index_only_shows_own_wines(): void
    {
        $ownWine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Mi Vino Propio',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);
        $otherWine = Wine::create([
            'user_id' => $this->makeOtherWinery()->id,
            'name' => 'Vino Ajeno Secreto',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);

        $this->get(route('winery.production-costs.index'))
            ->assertOk()
            ->assertSee($ownWine->name)
            ->assertDontSee($otherWine->name);
    }

    // --- Create ---

    public function test_create_renders(): void
    {
        $this->get(route('winery.production-costs.create'))->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('wine_id', null)
            ->set('category', '')
            ->set('description', '')
            ->set('amount', '')
            ->set('cost_date', '')
            ->call('save')
            ->assertHasErrors(['wine_id', 'category', 'description', 'amount', 'cost_date']);
    }

    public function test_create_saves_cost(): void
    {
        $wine = $this->makeWine();

        Livewire::test(Create::class)
            ->set('wine_id', $wine->id)
            ->set('category', 'analysis')
            ->set('description', 'Análisis básico')
            ->set('amount', '200.00')
            ->set('cost_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_costs', [
            'wine_id' => $wine->id,
            'user_id' => $this->winery->id,
            'description' => 'Análisis básico',
        ]);
    }

    public function test_create_rejects_wine_from_other_winery(): void
    {
        $otherWine = $this->makeWine($this->makeOtherWinery()->id);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Create::class)
            ->set('wine_id', $otherWine->id)
            ->set('category', 'analysis')
            ->set('description', 'Test')
            ->set('amount', '100.00')
            ->set('cost_date', now()->toDateString())
            ->call('save');
    }

    // --- Edit ---

    public function test_edit_renders(): void
    {
        $cost = $this->makeCost($this->makeWine());
        $this->get(route('winery.production-costs.edit', $cost))->assertOk();
    }

    public function test_other_winery_cannot_access_edit(): void
    {
        $cost = $this->makeCost($this->makeWine());
        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.production-costs.edit', $cost))
            ->assertForbidden();
    }

    public function test_edit_saves_changes(): void
    {
        $wine = $this->makeWine();
        $cost = $this->makeCost($wine);

        Livewire::test(Edit::class, ['cost' => $cost])
            ->set('description', 'Descripción actualizada')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_costs', [
            'id' => $cost->id,
            'description' => 'Descripción actualizada',
        ]);
    }

    public function test_other_winery_cannot_access_edit_to_delete(): void
    {
        $cost = $this->makeCost($this->makeWine());

        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.production-costs.edit', $cost))
            ->assertForbidden();
    }
}
