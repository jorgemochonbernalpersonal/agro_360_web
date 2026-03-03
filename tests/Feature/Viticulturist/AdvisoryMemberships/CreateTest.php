<?php

namespace Tests\Feature\Viticulturist\AdvisoryMemberships;

use App\Livewire\Viticulturist\AdvisoryMemberships\Create;
use App\Models\AdvisoryMembership;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_membership(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('advisor_name', 'Dr. Asesor Test')
            ->set('license_number', 'LIC-001')
            ->set('specialty', 'phytosanitary')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.advisory-memberships.index'));

        $this->assertDatabaseHas('advisory_memberships', [
            'viticulturist_id' => $viticulturist->id,
            'advisor_name'     => 'Dr. Asesor Test',
            'license_number'   => 'LIC-001',
            'specialty'        => 'phytosanitary',
            'active'           => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('advisor_name', '')
            ->set('license_number', '')
            ->call('save')
            ->assertHasErrors(['advisor_name', 'license_number']);
    }

    public function test_invalid_specialty_rejected(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('advisor_name', 'Asesor Test')
            ->set('license_number', 'LIC-001')
            ->set('specialty', 'invalid_specialty')
            ->call('save')
            ->assertHasErrors(['specialty']);
    }

    public function test_all_valid_specialties_accepted(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        foreach (array_keys(AdvisoryMembership::SPECIALTIES) as $i => $specialty) {
            Livewire::test(Create::class)
                ->set('advisor_name', "Asesor {$i}")
                ->set('license_number', "LIC-00{$i}")
                ->set('specialty', $specialty)
                ->call('save')
                ->assertHasNoErrors(['specialty']);
        }
    }

    public function test_optional_fields_saved(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('advisor_name', 'Asesor Completo')
            ->set('license_number', 'LIC-002')
            ->set('specialty', 'agronomy')
            ->set('company_name', 'Agronomía SA')
            ->set('phone', '600123456')
            ->set('email', 'asesor@test.com')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('advisory_memberships', [
            'viticulturist_id' => $viticulturist->id,
            'company_name'     => 'Agronomía SA',
            'phone'            => '600123456',
            'email'            => 'asesor@test.com',
        ]);
    }
}
