<?php

namespace Tests\Feature\Winery\WineLosses;

use App\Livewire\Winery\WineLosses\Create;
use App\Livewire\Winery\WineLosses\Edit;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineLoss;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class CreateEditTest extends WineryTestCase
{
    private \App\Models\User $winery;

    private UnitOfMeasurement $uom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
        $this->uom = UnitOfMeasurement::firstOrCreate(
            ['symbol' => 'L'],
            ['name' => 'Litros', 'type' => 'volume']
        );
    }

    private function makeWine(?int $userId = null): Wine
    {
        return Wine::create([
            'user_id'       => $userId ?? $this->winery->id,
            'name'          => 'Vino Test',
            'wine_type'     => 'red',
            'status'        => 'in_progress',
            'volume_liters' => 1000,
        ]);
    }

    private function makeLoss(Wine $wine): WineLoss
    {
        return WineLoss::create([
            'wine_id'               => $wine->id,
            'loss_type'             => 'evaporation',
            'loss_authorization'    => 'authorized',
            'quantity'              => 10,
            'unit_of_measurement_id' => $this->uom->id,
            'loss_date'             => now()->toDateString(),
            'created_by'            => $wine->user_id,
        ]);
    }

    // --- Create ---

    public function test_create_renders(): void
    {
        $this->get(route('winery.wine-losses.create'))->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('wine_id', '')
            ->set('loss_type', '')
            ->set('quantity', '')
            ->set('unit_of_measurement_id', '')
            ->set('loss_date', '')
            ->call('save')
            ->assertHasErrors(['wine_id', 'loss_type', 'quantity', 'unit_of_measurement_id', 'loss_date']);
    }

    public function test_create_rejects_wine_from_other_winery(): void
    {
        $otherWine = $this->makeWine($this->makeOtherWinery()->id);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $otherWine->id)
            ->set('loss_type', 'evaporation')
            ->set('quantity', '10')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('loss_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['wine_id']);
    }

    public function test_create_saves_loss(): void
    {
        $wine = $this->makeWine();

        Livewire::test(Create::class)
            ->set('wine_id', (string) $wine->id)
            ->set('loss_type', 'evaporation')
            ->set('quantity', '5')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('loss_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_losses', [
            'wine_id'    => $wine->id,
            'loss_type'  => 'evaporation',
            'created_by' => $this->winery->id,
        ]);
    }

    // --- Edit ---

    public function test_edit_renders(): void
    {
        $loss = $this->makeLoss($this->makeWine());
        $this->get(route('winery.wine-losses.edit', $loss))->assertOk();
    }

    public function test_other_winery_cannot_access_edit(): void
    {
        $loss = $this->makeLoss($this->makeWine());

        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.wine-losses.edit', $loss))
            ->assertForbidden();
    }

    public function test_edit_saves_changes(): void
    {
        $loss = $this->makeLoss($this->makeWine());

        Livewire::test(Edit::class, ['loss' => $loss])
            ->set('notes', 'Nota actualizada')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_losses', [
            'id'    => $loss->id,
            'notes' => 'Nota actualizada',
        ]);
    }
}
