<?php

namespace Tests\Feature\Viticulturist\Subcontracting;

use App\Livewire\Viticulturist\Subcontracting\Index;
use App\Models\Subcontracting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_viticulturist_can_view_index(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertStatus(200);
    }

    public function test_index_shows_own_records(): void
    {
        $viticulturist = $this->makeViticulturist();

        Subcontracting::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'company_name'     => 'Empresa Propia SA',
            'service_date'     => now()->toDateString(),
            'service_type'     => 'harvesting',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Empresa Propia SA');
    }

    public function test_index_does_not_show_other_viticulturist_records(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();

        Subcontracting::factory()->create([
            'viticulturist_id' => $other->id,
            'company_name'     => 'Empresa Ajena SL',
            'service_date'     => now()->toDateString(),
            'service_type'     => 'pruning',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertDontSee('Empresa Ajena SL');
    }

    public function test_delete_own_record(): void
    {
        $viticulturist = $this->makeViticulturist();

        $record = Subcontracting::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'company_name'     => 'A Borrar',
            'service_date'     => now()->toDateString(),
            'service_type'     => 'transport',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $record->id);

        $this->assertDatabaseMissing('subcontractings', ['id' => $record->id]);
    }

    public function test_toggle_invoiced_status(): void
    {
        $viticulturist = $this->makeViticulturist();

        $record = Subcontracting::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'company_name'     => 'Empresa Toggle',
            'service_date'     => now()->toDateString(),
            'service_type'     => 'treatment',
            'invoiced'         => false,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('toggleInvoiced', $record->id);

        $this->assertDatabaseHas('subcontractings', [
            'id'       => $record->id,
            'invoiced' => true,
        ]);
    }

    public function test_cannot_delete_other_viticulturist_record(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();

        $record = Subcontracting::factory()->create([
            'viticulturist_id' => $other->id,
            'company_name'     => 'Ajeno',
            'service_date'     => now()->toDateString(),
            'service_type'     => 'other',
        ]);

        $this->actingAs($viticulturist);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $record->id);
    }
}
