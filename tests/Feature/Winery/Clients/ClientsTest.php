<?php

namespace Tests\Feature\Winery\Clients;

use App\Livewire\Winery\Clients\Create;
use App\Models\Client;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class ClientsTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_index_renders(): void
    {
        $this->get(route('winery.clients.index'))->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        // Blank client_type to trigger required validation
        Livewire::test(Create::class)
            ->set('client_type', '')
            ->call('save')
            ->assertHasErrors(['client_type']);
    }

    public function test_create_validates_individual_requires_first_name(): void
    {
        Livewire::test(Create::class)
            ->set('client_type', 'individual')
            ->set('first_name', '')
            ->call('save')
            ->assertHasErrors(['first_name']);
    }

    public function test_create_validates_company_requires_company_name_and_document(): void
    {
        Livewire::test(Create::class)
            ->set('client_type', 'company')
            ->set('company_name', '')
            ->set('company_document', '')
            ->call('save')
            ->assertHasErrors(['company_name', 'company_document']);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $client = Client::create([
            'user_id'     => $this->winery->id,
            'client_type' => 'individual',
            'first_name'  => 'Test',
            'active'      => true,
        ]);

        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.clients.edit', $client))
            ->assertForbidden();
    }
}
