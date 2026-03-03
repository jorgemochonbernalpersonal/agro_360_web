<?php

namespace Tests\Feature\Viticulturist\CommercialAuthorizations;

use App\Livewire\Viticulturist\CommercialAuthorizations\Create;
use App\Models\CommercialAuthorization;
use App\Models\Exploitation;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    private function makeExploitation(int $viticulturistId): Exploitation
    {
        return Exploitation::create([
            'viticulturist_id'         => $viticulturistId,
            'exploitation_name'        => 'Explotación Test',
            'holder_name'              => 'Test Holder',
            'holder_nif'               => '12345678A',
            'is_ecological'            => false,
            'is_integrated_production' => false,
            'is_quality_scheme'        => false,
            'active'                   => true,
        ]);
    }

    public function test_can_create_authorization(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('authorization_type', 'do_registration')
            ->set('issue_date', '2024-01-15')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.commercial-authorizations.index'));

        $this->assertDatabaseHas('commercial_authorizations', [
            'viticulturist_id'   => $viticulturist->id,
            'authorization_type' => 'do_registration',
            'active'             => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('authorization_type', '')
            ->set('issue_date', '')
            ->call('save')
            ->assertHasErrors(['authorization_type', 'issue_date']);
    }

    public function test_expiry_must_be_after_issue(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('authorization_type', 'do_registration')
            ->set('issue_date', '2024-06-01')
            ->set('expiry_date', '2024-05-01')
            ->call('save')
            ->assertHasErrors(['expiry_date']);
    }

    public function test_exploitation_scoped_to_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $otherExp      = $this->makeExploitation($other->id);

        $this->actingAs($viticulturist);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Create::class)
            ->set('authorization_type', 'do_registration')
            ->set('issue_date', '2024-01-15')
            ->set('exploitation_id', (string) $otherExp->id)
            ->call('save');
    }

    public function test_all_authorization_types_accepted(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        foreach (array_keys(CommercialAuthorization::AUTHORIZATION_TYPES) as $type) {
            Livewire::test(Create::class)
                ->set('authorization_type', $type)
                ->set('issue_date', '2024-01-15')
                ->call('save')
                ->assertHasNoErrors(['authorization_type']);
        }
    }
}
