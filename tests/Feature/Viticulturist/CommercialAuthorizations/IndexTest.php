<?php

namespace Tests\Feature\Viticulturist\CommercialAuthorizations;

use App\Livewire\Viticulturist\CommercialAuthorizations\Index;
use App\Models\CommercialAuthorization;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    private function makeAuthorization(int $viticulturistId, string $type = 'do_registration'): CommercialAuthorization
    {
        return CommercialAuthorization::create([
            'viticulturist_id'   => $viticulturistId,
            'authorization_type' => $type,
            'issue_date'         => '2024-01-01',
            'active'             => true,
        ]);
    }

    public function test_index_shows_active_authorizations(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeAuthorization($viticulturist->id, 'organic_certification');

        $this->actingAs($viticulturist);

        // The index renders the type label
        Livewire::test(Index::class)
            ->assertSee('Certificación Ecológica');
    }

    public function test_deactivate_sets_active_to_false(): void
    {
        $viticulturist = $this->makeViticulturist();
        $auth          = $this->makeAuthorization($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('deactivate', $auth->id);

        $this->assertDatabaseHas('commercial_authorizations', [
            'id'     => $auth->id,
            'active' => false,
        ]);
    }

    public function test_deactivated_disappears_from_list(): void
    {
        $viticulturist = $this->makeViticulturist();
        // Use a unique issuing_body so we can assert it disappears from the table rows
        CommercialAuthorization::create([
            'viticulturist_id'   => $viticulturist->id,
            'authorization_type' => 'planting_right',
            'issue_date'         => '2024-01-01',
            'issuing_body'       => 'Organismo-Plantacion-Unico',
            'active'             => true,
        ]);

        $auth = CommercialAuthorization::where('issuing_body', 'Organismo-Plantacion-Unico')->first();

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Organismo-Plantacion-Unico')
            ->call('deactivate', $auth->id)
            ->assertDontSee('Organismo-Plantacion-Unico');
    }

    public function test_filter_by_type(): void
    {
        $viticulturist = $this->makeViticulturist();

        CommercialAuthorization::create([
            'viticulturist_id'   => $viticulturist->id,
            'authorization_type' => 'do_registration',
            'issue_date'         => '2024-01-01',
            'authorization_code' => 'CODE-DO-REG-001',
            'active'             => true,
        ]);
        CommercialAuthorization::create([
            'viticulturist_id'   => $viticulturist->id,
            'authorization_type' => 'organic_certification',
            'issue_date'         => '2024-01-01',
            'authorization_code' => 'CODE-ORGANIC-001',
            'active'             => true,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('filterType', 'do_registration')
            ->assertSee('CODE-DO-REG-001')
            ->assertDontSee('CODE-ORGANIC-001');
    }

    public function test_cannot_deactivate_other_viticulturists_authorization(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $auth          = $this->makeAuthorization($viticulturist->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('deactivate', $auth->id);
    }
}
