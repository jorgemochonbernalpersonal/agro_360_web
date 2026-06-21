<?php

namespace Tests\Feature\Winery\WineTransfers;

use App\Livewire\Winery\WineTransfers\Create;
use App\Livewire\Winery\WineTransfers\Edit;
use App\Models\Container;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineTransfer;
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

    // --- Create ---

    public function test_create_renders(): void
    {
        $this->get(route('winery.wine-transfers.create'))->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('wine_id', '')
            ->set('to_container_id', '')
            ->set('quantity', '')
            ->set('unit_of_measurement_id', '')
            ->set('transfer_date', '')
            ->call('save')
            ->assertHasErrors(['wine_id', 'to_container_id', 'quantity', 'unit_of_measurement_id', 'transfer_date']);
    }

    public function test_create_rejects_wine_from_other_winery(): void
    {
        $otherWine = $this->makeWine($this->makeOtherWinery()->id);
        $container = $this->makeContainer();

        Livewire::test(Create::class)
            ->set('wine_id', (string) $otherWine->id)
            ->set('to_container_id', (string) $container->id)
            ->set('quantity', '100')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('transfer_type', 'racking')
            ->set('transfer_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['wine_id']);
    }

    public function test_create_rejects_container_from_other_winery(): void
    {
        $wine = $this->makeWine();
        $otherContainer = $this->makeContainer($this->makeOtherWinery()->id);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $wine->id)
            ->set('to_container_id', (string) $otherContainer->id)
            ->set('quantity', '100')
            ->set('unit_of_measurement_id', (string) $this->uom->id)
            ->set('transfer_type', 'racking')
            ->set('transfer_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['to_container_id']);
    }

    // --- Edit ---

    public function test_edit_renders(): void
    {
        $transfer = $this->makeTransfer($this->makeWine());
        $this->get(route('winery.wine-transfers.edit', $transfer))->assertOk();
    }

    public function test_other_winery_cannot_access_edit(): void
    {
        $transfer = $this->makeTransfer($this->makeWine());

        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.wine-transfers.edit', $transfer))
            ->assertForbidden();
    }

    public function test_edit_saves_changes(): void
    {
        $transfer = $this->makeTransfer($this->makeWine());

        Livewire::test(Edit::class, ['transfer' => $transfer])
            ->set('notes', 'Actualizado en test')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_transfers', [
            'id' => $transfer->id,
            'notes' => 'Actualizado en test',
        ]);
    }

    private function makeWine(?int $userId = null): Wine
    {
        return Wine::create([
            'user_id' => $userId ?? $this->winery->id,
            'name' => 'Vino Test',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);
    }

    private function makeContainer(?int $userId = null): Container
    {
        return Container::create([
            'user_id' => $userId ?? $this->winery->id,
            'name' => 'Depósito Test',
            'capacity' => 5000,
        ]);
    }

    private function makeTransfer(Wine $wine): WineTransfer
    {
        $dest = $this->makeContainer($wine->user_id);

        return WineTransfer::create([
            'wine_id' => $wine->id,
            'to_container_id' => $dest->id,
            'quantity' => 100,
            'unit_of_measurement_id' => $this->uom->id,
            'transfer_type' => 'racking',
            'transfer_date' => now()->toDateString(),
            'created_by' => $wine->user_id,
        ]);
    }
}
