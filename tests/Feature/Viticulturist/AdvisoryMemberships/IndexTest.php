<?php

namespace Tests\Feature\Viticulturist\AdvisoryMemberships;

use App\Livewire\Viticulturist\AdvisoryMemberships\Index;
use App\Models\AdvisoryMembership;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_index_shows_active_memberships(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeMembership($viticulturist->id, 'Mi Asesor Visible');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Mi Asesor Visible');
    }

    public function test_deactivate_sets_active_to_false(): void
    {
        $viticulturist = $this->makeViticulturist();
        $membership = $this->makeMembership($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('deactivate', $membership->id);

        $this->assertDatabaseHas('advisory_memberships', [
            'id' => $membership->id,
            'active' => false,
        ]);
    }

    public function test_deactivated_disappears_from_list(): void
    {
        $viticulturist = $this->makeViticulturist();
        $membership = $this->makeMembership($viticulturist->id, 'Asesor a Dar de Baja');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('deactivate', $membership->id)
            ->assertDontSee('Asesor a Dar de Baja');
    }

    public function test_cannot_deactivate_other_viticulturists_membership(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $membership = $this->makeMembership($viticulturist->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('deactivate', $membership->id);
    }

    private function makeMembership(int $viticulturistId, string $advisorName = 'Asesor Test'): AdvisoryMembership
    {
        return AdvisoryMembership::create([
            'viticulturist_id' => $viticulturistId,
            'advisor_name' => $advisorName,
            'license_number' => 'LIC-'.uniqid(),
            'specialty' => 'phytosanitary',
            'active' => true,
        ]);
    }
}
