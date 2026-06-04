<?php

namespace Tests\Feature\Viticulturist\AdvisoryMemberships;

use App\Livewire\Viticulturist\AdvisoryMemberships\Edit;
use App\Models\AdvisoryMembership;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_mount_fills_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $membership = $this->makeMembership($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['advisoryMembership' => $membership])
            ->assertSet('advisor_name', 'Asesor Original')
            ->assertSet('license_number', 'LIC-ORIG-001')
            ->assertSet('specialty', 'phytosanitary');
    }

    public function test_can_update_membership(): void
    {
        $viticulturist = $this->makeViticulturist();
        $membership = $this->makeMembership($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['advisoryMembership' => $membership])
            ->set('advisor_name', 'Asesor Actualizado')
            ->set('license_number', 'LIC-NEW-001')
            ->set('specialty', 'oenology')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.advisory-memberships.index'));

        $this->assertDatabaseHas('advisory_memberships', [
            'id' => $membership->id,
            'advisor_name' => 'Asesor Actualizado',
            'license_number' => 'LIC-NEW-001',
            'specialty' => 'oenology',
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $membership = $this->makeMembership($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['advisoryMembership' => $membership])
            ->set('advisor_name', '')
            ->set('license_number', '')
            ->call('save')
            ->assertHasErrors(['advisor_name', 'license_number']);
    }

    public function test_invalid_email_rejected(): void
    {
        $viticulturist = $this->makeViticulturist();
        $membership = $this->makeMembership($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['advisoryMembership' => $membership])
            ->set('email', 'not-an-email')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_cannot_edit_other_viticulturists_membership(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $membership = $this->makeMembership($viticulturist->id);

        $this->actingAs($other)
            ->get(route('viticulturist.advisory-memberships.edit', $membership))
            ->assertStatus(403);
    }

    private function makeMembership(int $viticulturistId): AdvisoryMembership
    {
        return AdvisoryMembership::create([
            'viticulturist_id' => $viticulturistId,
            'advisor_name' => 'Asesor Original',
            'license_number' => 'LIC-ORIG-001',
            'specialty' => 'phytosanitary',
            'active' => true,
        ]);
    }
}
