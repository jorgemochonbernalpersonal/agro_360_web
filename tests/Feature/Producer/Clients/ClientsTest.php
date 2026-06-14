<?php

namespace Tests\Feature\Producer\Clients;

use App\Livewire\Clients\Create as WineryClientCreate;
use App\Models\Client;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

/**
 * Producer tiene dos tipos de clientes:
 * - Clientes viticultor (uva): producer.clients.*
 * - Clientes bodega (vino): producer.winery-clients.*
 */
class ClientsTest extends ProducerTestCase
{
    private User $producer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->producer = $this->makeProducer();
        $this->actingAs($this->producer);
    }

    public function test_viticulturist_clients_index_renders(): void
    {
        $this->get(route('producer.clients.index'))->assertOk();
    }

    public function test_winery_clients_index_renders(): void
    {
        $this->get(route('producer.winery-clients.index'))->assertOk();
    }

    public function test_winery_clients_insights_renders(): void
    {
        $this->get(route('producer.winery-clients.insights'))->assertOk();
    }

    public function test_winery_client_create_validates_required_fields(): void
    {
        Livewire::test(WineryClientCreate::class)
            ->set('client_type', '')
            ->call('save')
            ->assertHasErrors(['client_type']);
    }

    public function test_winery_client_create_validates_company_fields(): void
    {
        Livewire::test(WineryClientCreate::class)
            ->set('client_type', 'company')
            ->set('company_name', '')
            ->set('company_document', '')
            ->call('save')
            ->assertHasErrors(['company_name', 'company_document']);
    }

    public function test_other_producer_cannot_edit_winery_client(): void
    {
        $client = Client::create([
            'user_id' => $this->producer->id,
            'client_type' => 'individual',
            'first_name' => 'Private Client',
            'active' => true,
        ]);

        $this->actingAs($this->makeOtherProducer())
            ->get(route('producer.winery-clients.edit', $client))
            ->assertForbidden();
    }
}
