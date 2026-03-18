<?php

namespace Tests\Feature\Viticulturist\AgriInsurance;

use App\Livewire\Viticulturist\AgriInsurance\Edit;
use App\Models\AgriInsurance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_viticulturist_can_edit_own_insurance(): void
    {
        $viticulturist = $this->makeViticulturist();

        $insurance = AgriInsurance::factory()->create([
            'viticulturist_id'  => $viticulturist->id,
            'insurance_company' => 'Original',
            'coverage_type'     => 'hail',
            'start_date'        => now()->toDateString(),
            'end_date'          => now()->addYear()->toDateString(),
            'status'            => 'active',
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['insurance' => $insurance])
            ->set('insurance_company', 'Actualizada SA')
            ->set('status', 'expired')
            ->call('update')
            ->assertRedirect(route('viticulturist.agri-insurance.index'));

        $this->assertDatabaseHas('agri_insurances', [
            'id'                => $insurance->id,
            'insurance_company' => 'Actualizada SA',
            'status'            => 'expired',
        ]);
    }

    public function test_cannot_edit_other_viticulturist_insurance(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();

        $insurance = AgriInsurance::factory()->create([
            'viticulturist_id'  => $other->id,
            'insurance_company' => 'Ajena',
            'coverage_type'     => 'frost',
            'start_date'        => now()->toDateString(),
            'end_date'          => now()->addYear()->toDateString(),
            'status'            => 'active',
        ]);

        $this->actingAs($viticulturist);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        Livewire::test(Edit::class, ['insurance' => $insurance]);
    }

    public function test_mount_populates_all_fields(): void
    {
        $viticulturist = $this->makeViticulturist();

        $insurance = AgriInsurance::factory()->create([
            'viticulturist_id'  => $viticulturist->id,
            'insurance_company' => 'Agroseguro',
            'coverage_type'     => 'comprehensive',
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-12-31',
            'status'            => 'active',
            'premium'           => 500.00,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['insurance' => $insurance])
            ->assertSet('insurance_company', 'Agroseguro')
            ->assertSet('coverage_type', 'comprehensive')
            ->assertSet('status', 'active')
            ->assertSet('start_date', '2026-01-01')
            ->assertSet('end_date', '2026-12-31');
    }
}
