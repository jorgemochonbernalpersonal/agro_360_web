<?php

namespace Tests\Feature\Viticulturist\Subcontracting;

use App\Livewire\Viticulturist\Subcontracting\Edit;
use App\Models\Subcontracting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_viticulturist_can_edit_own_record(): void
    {
        $viticulturist = $this->makeViticulturist();

        $record = Subcontracting::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'company_name'     => 'Original',
            'service_type'     => 'pruning',
            'service_date'     => now()->toDateString(),
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['record' => $record])
            ->set('company_name', 'Actualizada SL')
            ->call('update')
            ->assertRedirect(route('viticulturist.subcontracting.index'));

        $this->assertDatabaseHas('subcontractings', [
            'id'           => $record->id,
            'company_name' => 'Actualizada SL',
        ]);
    }

    public function test_cannot_edit_other_viticulturist_record(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();

        $record = Subcontracting::factory()->create([
            'viticulturist_id' => $other->id,
            'company_name'     => 'Ajeno',
            'service_type'     => 'harvesting',
            'service_date'     => now()->toDateString(),
        ]);

        $this->actingAs($viticulturist);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        Livewire::test(Edit::class, ['record' => $record]);
    }

    public function test_mount_populates_all_fields(): void
    {
        $viticulturist = $this->makeViticulturist();

        $record = Subcontracting::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'company_name'     => 'Empresa Test',
            'service_type'     => 'treatment',
            'service_date'     => '2026-03-15',
            'invoiced'         => true,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['record' => $record])
            ->assertSet('company_name', 'Empresa Test')
            ->assertSet('service_type', 'treatment')
            ->assertSet('service_date', '2026-03-15')
            ->assertSet('invoiced', true);
    }
}
