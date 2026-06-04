<?php

namespace Tests\Feature\Viticulturist\AgriInsurance;

use App\Livewire\Viticulturist\AgriInsurance\Index;
use App\Models\AgriInsurance;
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

    public function test_index_shows_own_insurances(): void
    {
        $viticulturist = $this->makeViticulturist();

        AgriInsurance::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'insurance_company' => 'AGROSEGURO SA',
            'coverage_type' => 'hail',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('AGROSEGURO SA');
    }

    public function test_index_does_not_show_other_viticulturist_insurances(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();

        AgriInsurance::factory()->create([
            'viticulturist_id' => $other->id,
            'insurance_company' => 'Aseguradora Ajena',
            'coverage_type' => 'frost',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertDontSee('Aseguradora Ajena');
    }

    public function test_delete_own_insurance(): void
    {
        $viticulturist = $this->makeViticulturist();

        $insurance = AgriInsurance::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'insurance_company' => 'A Borrar',
            'coverage_type' => 'comprehensive',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $insurance->id);

        $this->assertDatabaseMissing('agri_insurances', ['id' => $insurance->id]);
    }

    public function test_filter_by_status(): void
    {
        $viticulturist = $this->makeViticulturist();

        AgriInsurance::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'insurance_company' => 'Activa SA',
            'coverage_type' => 'hail',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'status' => 'active',
        ]);
        AgriInsurance::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'insurance_company' => 'Vencida SL',
            'coverage_type' => 'frost',
            'start_date' => now()->subYear()->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
            'status' => 'expired',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('filter_status', 'active')
            ->assertSee('Activa SA')
            ->assertDontSee('Vencida SL');
    }
}
