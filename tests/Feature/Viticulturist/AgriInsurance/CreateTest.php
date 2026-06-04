<?php

namespace Tests\Feature\Viticulturist\AgriInsurance;

use App\Livewire\Viticulturist\AgriInsurance\Create;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_viticulturist_can_create_insurance(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('insurance_company', 'Mapfre Agro')
            ->set('coverage_type', 'hail')
            ->set('start_date', now()->toDateString())
            ->set('end_date', now()->addYear()->toDateString())
            ->set('status', 'active')
            ->call('save')
            ->assertRedirect(route('viticulturist.agri-insurance.index'));

        $this->assertDatabaseHas('agri_insurances', [
            'viticulturist_id' => $viticulturist->id,
            'insurance_company' => 'Mapfre Agro',
            'coverage_type' => 'hail',
        ]);
    }

    public function test_insurance_company_is_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('insurance_company', '')
            ->set('coverage_type', 'hail')
            ->set('start_date', now()->toDateString())
            ->set('end_date', now()->addYear()->toDateString())
            ->call('save')
            ->assertHasErrors(['insurance_company']);
    }

    public function test_end_date_must_be_after_start_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('insurance_company', 'Test')
            ->set('coverage_type', 'comprehensive')
            ->set('start_date', '2026-03-18')
            ->set('end_date', '2026-03-01')
            ->call('save')
            ->assertHasErrors(['end_date']);
    }

    public function test_coverage_type_must_be_valid(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('insurance_company', 'Test')
            ->set('coverage_type', 'invalid')
            ->set('start_date', now()->toDateString())
            ->set('end_date', now()->addYear()->toDateString())
            ->call('save')
            ->assertHasErrors(['coverage_type']);
    }

    public function test_mount_sets_default_dates(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertSet('start_date', now()->format('Y-m-d'))
            ->assertSet('end_date', now()->addYear()->format('Y-m-d'));
    }
}
