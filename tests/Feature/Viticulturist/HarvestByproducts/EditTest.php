<?php

namespace Tests\Feature\Viticulturist\HarvestByproducts;

use App\Livewire\Viticulturist\HarvestByproducts\Edit;
use App\Models\Campaign;
use App\Models\HarvestByproduct;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_can_edit_harvest_byproduct(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $byproduct = $this->makeByproduct($v->id, $c->id);

        $this->actingAs($v);

        Livewire::test(Edit::class, ['byproduct' => $byproduct])
            ->set('destination_name', 'Destilería Actualizada')
            ->set('quantity_kg', '1200.000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.harvest-byproducts.index'));

        $this->assertDatabaseHas('harvest_byproducts', [
            'id' => $byproduct->id,
            'destination_name' => 'Destilería Actualizada',
        ]);
    }

    public function test_mount_fills_properties_from_model(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $byproduct = $this->makeByproduct($v->id, $c->id);

        $this->actingAs($v);

        Livewire::test(Edit::class, ['byproduct' => $byproduct])
            ->assertSet('byproduct_type', 'pomace')
            ->assertSet('destination_name', 'Cooperativa Original');
    }

    public function test_cannot_edit_other_viticulturists_record(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $byproduct = $this->makeByproduct($v->id, $c->id);

        $this->actingAs($other);

        Livewire::test(Edit::class, ['byproduct' => $byproduct])
            ->assertStatus(403);
    }

    public function test_validates_quantity_on_update(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $byproduct = $this->makeByproduct($v->id, $c->id);

        $this->actingAs($v);

        Livewire::test(Edit::class, ['byproduct' => $byproduct])
            ->set('quantity_kg', '-5')
            ->call('save')
            ->assertHasErrors(['quantity_kg']);
    }

    private function makeByproduct(int $viticulturistId, int $campaignId): HarvestByproduct
    {
        return HarvestByproduct::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id' => $campaignId,
            'date' => '2024-10-01',
            'byproduct_type' => 'pomace',
            'quantity_kg' => 1000.000,
            'destination_type' => 'cooperative',
            'destination_name' => 'Cooperativa Original',
        ]);
    }
}
