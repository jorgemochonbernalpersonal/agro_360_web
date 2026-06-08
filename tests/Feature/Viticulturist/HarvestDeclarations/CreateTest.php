<?php

namespace Tests\Feature\Viticulturist\HarvestDeclarations;

use App\Livewire\Viticulturist\HarvestDeclarations\Create;
use App\Models\Campaign;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_harvest_declaration(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('declaration_year', '2024')
            ->set('declaration_date', '2024-11-15')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.harvest-declarations.index'));

        $this->assertDatabaseHas('harvest_declarations', [
            'viticulturist_id' => $v->id,
            'declaration_year' => 2024,
            'status' => 'draft',
            'active' => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('campaign_id', '')
            ->set('declaration_year', '')
            ->set('declaration_date', '')
            ->call('save')
            ->assertHasErrors(['campaign_id', 'declaration_year', 'declaration_date']);
    }

    public function test_declaration_year_must_be_valid(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('declaration_year', '1999')
            ->set('declaration_date', '2024-11-15')
            ->call('save')
            ->assertHasErrors(['declaration_year']);
    }

    public function test_mount_sets_defaults(): void
    {
        $v = $this->makeViticulturist();
        $campaign = Campaign::getOrCreateActiveForYear($v->id);
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->assertSet('campaign_id', (string) $campaign->id)
            ->assertSet('declaration_year', (string) now()->year)
            ->assertSet('declaration_date', now()->format('Y-m-d'));
    }
}
