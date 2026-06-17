<?php

namespace Tests\Feature\Winery\Labeling;

use App\Livewire\Winery\Labeling\Create;
use App\Livewire\Winery\Labeling\Edit;
use App\Models\Wine;
use App\Models\WineLabeling;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class CreateEditTest extends WineryTestCase
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
            'user_id'   => $userId ?? $this->winery->id,
            'name'      => 'Vino Test',
            'wine_type' => 'red',
            'status'    => 'bottled',
        ]);
    }

    private function makeLabeling(Wine $wine): WineLabeling
    {
        return WineLabeling::create([
            'user_id'          => $wine->user_id,
            'wine_id'          => $wine->id,
            'labeling_date'    => now()->toDateString(),
            'quantity_labeled' => 100,
            'created_by'       => $wine->user_id,
        ]);
    }

    // --- Create ---

    public function test_create_renders(): void
    {
        $this->get(route('winery.labeling.create'))->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('wine_id', '')
            ->set('labeling_date', '')
            ->set('quantity_labeled', '')
            ->call('save')
            ->assertHasErrors(['wine_id', 'labeling_date', 'quantity_labeled']);
    }

    public function test_create_saves_labeling(): void
    {
        $wine = $this->makeWine();

        Livewire::test(Create::class)
            ->set('wine_id', (string) $wine->id)
            ->set('labeling_date', now()->toDateString())
            ->set('quantity_labeled', '150')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_labelings', [
            'user_id'          => $this->winery->id,
            'wine_id'          => $wine->id,
            'quantity_labeled' => 150,
        ]);
    }

    public function test_create_rejects_wine_from_other_winery(): void
    {
        $otherWine = $this->makeWine($this->makeOtherWinery()->id);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $otherWine->id)
            ->set('labeling_date', now()->toDateString())
            ->set('quantity_labeled', '100')
            ->call('save')
            ->assertHasErrors(['wine_id']);
    }

    // --- Edit ---

    public function test_edit_renders(): void
    {
        $labeling = $this->makeLabeling($this->makeWine());
        $this->get(route('winery.labeling.edit', $labeling))->assertOk();
    }

    public function test_other_winery_cannot_access_edit(): void
    {
        $labeling = $this->makeLabeling($this->makeWine());

        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.labeling.edit', $labeling))
            ->assertForbidden();
    }

    public function test_edit_saves_changes(): void
    {
        $labeling = $this->makeLabeling($this->makeWine());

        Livewire::test(Edit::class, ['labeling' => $labeling])
            ->set('quantity_labeled', '200')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_labelings', [
            'id'               => $labeling->id,
            'quantity_labeled' => 200,
        ]);
    }
}
