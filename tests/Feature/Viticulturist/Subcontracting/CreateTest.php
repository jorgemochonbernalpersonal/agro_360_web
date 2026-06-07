<?php

namespace Tests\Feature\Viticulturist\Subcontracting;

use App\Livewire\Viticulturist\Subcontracting\Create;
use App\Models\Campaign;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_viticulturist_can_create_subcontracting(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('company_name', 'Viticultores del Norte SL')
            ->set('service_type', 'harvesting')
            ->set('service_date', now()->toDateString())
            ->call('save')
            ->assertRedirect(route('viticulturist.subcontracting.index'));

        $this->assertDatabaseHas('subcontractings', [
            'viticulturist_id' => $viticulturist->id,
            'company_name' => 'Viticultores del Norte SL',
            'service_type' => 'harvesting',
        ]);
    }

    public function test_company_name_is_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('company_name', '')
            ->set('service_type', 'harvesting')
            ->set('service_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['company_name']);
    }

    public function test_service_type_must_be_valid(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('company_name', 'Test')
            ->set('service_type', 'invalid_type')
            ->set('service_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['service_type']);
    }

    public function test_service_date_is_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('company_name', 'Test')
            ->set('service_type', 'pruning')
            ->set('service_date', '')
            ->call('save')
            ->assertHasErrors(['service_date']);
    }

    public function test_end_date_must_be_after_start_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('company_name', 'Test')
            ->set('service_type', 'pruning')
            ->set('service_date', '2026-03-10')
            ->set('service_end_date', '2026-03-05')
            ->call('save')
            ->assertHasErrors(['service_end_date']);
    }

    public function test_mount_preselects_active_campaign(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = Campaign::where('viticulturist_id', $viticulturist->id)
            ->where('active', true)
            ->first();

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertSet('campaign_id', (string) $campaign->id);
    }

    public function test_invoiced_defaults_to_false(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertSet('invoiced', false);
    }
}
