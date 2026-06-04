<?php

namespace Tests\Feature\Viticulturist\CommercialAuthorizations;

use App\Livewire\Viticulturist\CommercialAuthorizations\Edit;
use App\Models\CommercialAuthorization;
use App\Models\Exploitation;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_mount_fills_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $auth = $this->makeAuthorization($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['commercialAuthorization' => $auth])
            ->assertSet('authorization_type', 'do_registration')
            ->assertSet('issue_date', '2024-01-01')
            ->assertSet('expiry_date', '');
    }

    public function test_can_update_authorization(): void
    {
        $viticulturist = $this->makeViticulturist();
        $auth = $this->makeAuthorization($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['commercialAuthorization' => $auth])
            ->set('authorization_type', 'organic_certification')
            ->set('issue_date', '2024-03-01')
            ->set('issuing_body', 'CCPAE')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.commercial-authorizations.index'));

        $this->assertDatabaseHas('commercial_authorizations', [
            'id' => $auth->id,
            'authorization_type' => 'organic_certification',
            'issuing_body' => 'CCPAE',
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $auth = $this->makeAuthorization($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['commercialAuthorization' => $auth])
            ->set('authorization_type', '')
            ->set('issue_date', '')
            ->call('save')
            ->assertHasErrors(['authorization_type', 'issue_date']);
    }

    public function test_exploitation_ownership_enforced_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $auth = $this->makeAuthorization($viticulturist->id);
        $otherExp = $this->makeExploitation($other->id);

        $this->actingAs($viticulturist);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Edit::class, ['commercialAuthorization' => $auth])
            ->set('exploitation_id', (string) $otherExp->id)
            ->call('save');
    }

    public function test_cannot_edit_other_viticulturists_authorization(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $auth = $this->makeAuthorization($viticulturist->id);

        $this->actingAs($other)
            ->get(route('viticulturist.commercial-authorizations.edit', $auth))
            ->assertStatus(403);
    }

    private function makeExploitation(int $viticulturistId): Exploitation
    {
        return Exploitation::create([
            'viticulturist_id' => $viticulturistId,
            'exploitation_name' => 'Explotación Test',
            'holder_name' => 'Test Holder',
            'holder_nif' => '12345678A',
            'is_ecological' => false,
            'is_integrated_production' => false,
            'is_quality_scheme' => false,
            'active' => true,
        ]);
    }

    private function makeAuthorization(int $viticulturistId): CommercialAuthorization
    {
        return CommercialAuthorization::create([
            'viticulturist_id' => $viticulturistId,
            'authorization_type' => 'do_registration',
            'issue_date' => '2024-01-01',
            'active' => true,
        ]);
    }
}
