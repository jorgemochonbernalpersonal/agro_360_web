<?php

namespace Tests\Feature\Viticulturist\HarvestByproducts;

use App\Livewire\Viticulturist\HarvestByproducts\Create;
use App\Models\Campaign;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_harvest_byproduct(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('date', '2024-10-15')
            ->set('byproduct_type', 'raspon')
            ->set('quantity_kg', '800.000')
            ->set('destination_type', 'destileria')
            ->set('destination_name', 'Destilería García S.L.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.harvest-byproducts.index'));

        $this->assertDatabaseHas('harvest_byproducts', [
            'viticulturist_id' => $v->id,
            'byproduct_type' => 'raspon',
            'destination_name' => 'Destilería García S.L.',
            'active' => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('campaign_id', '')
            ->set('date', '')
            ->set('quantity_kg', '')
            ->set('destination_name', '')
            ->call('save')
            ->assertHasErrors(['campaign_id', 'date', 'quantity_kg', 'destination_name']);
    }

    public function test_quantity_must_be_positive(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('quantity_kg', '0')
            ->call('save')
            ->assertHasErrors(['quantity_kg']);
    }

    public function test_byproduct_type_must_be_valid_enum(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('byproduct_type', 'invalid_type')
            ->call('save')
            ->assertHasErrors(['byproduct_type']);
    }

    public function test_mount_auto_fills_campaign_and_date(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->assertSet('campaign_id', (string) $c->id)
            ->assertSet('date', now()->format('Y-m-d'));
    }

    public function test_optional_document_reference_can_be_empty(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('date', '2024-10-15')
            ->set('byproduct_type', 'lia')
            ->set('quantity_kg', '300')
            ->set('destination_type', 'compostaje')
            ->set('destination_name', 'Planta Compostaje SA')
            ->set('document_reference', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvest_byproducts', [
            'viticulturist_id' => $v->id,
            'document_reference' => null,
        ]);
    }
}
