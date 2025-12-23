<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $client = Client::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals($user->id, $client->user->id);
    }

    public function test_client_has_many_addresses(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $address1 = ClientAddress::create([
            'client_id' => $client->id,
            'address' => 'Calle Test 123',
            'postal_code' => '28001',
        ]);
        $address2 = ClientAddress::create([
            'client_id' => $client->id,
            'address' => 'Calle Test 456',
            'postal_code' => '28002',
        ]);

        $this->assertCount(2, $client->addresses);
    }

    public function test_client_has_default_address(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $defaultAddress = ClientAddress::create([
            'client_id' => $client->id,
            'address' => 'Calle Test 123',
            'postal_code' => '28001',
            'is_default' => true,
        ]);

        $otherAddress = ClientAddress::create([
            'client_id' => $client->id,
            'address' => 'Calle Test 456',
            'postal_code' => '28002',
            'is_default' => false,
        ]);

        $this->assertEquals($defaultAddress->id, $client->defaultAddress->id);
    }

    public function test_client_has_many_invoices(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $invoice1 = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $invoice2 = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $this->assertCount(2, $client->invoices);
    }

    public function test_get_full_name_returns_company_name_for_company(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $client = Client::factory()->create([
            'user_id' => $user->id,
            'client_type' => 'company',
            'company_name' => 'Empresa Test S.L.',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);

        $this->assertEquals('Empresa Test S.L.', $client->full_name);
    }

    public function test_get_full_name_returns_full_name_for_individual(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $client = Client::factory()->create([
            'user_id' => $user->id,
            'client_type' => 'individual',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'company_name' => null,
        ]);

        $this->assertEquals('Juan Pérez', $client->full_name);
    }

    public function test_get_full_name_handles_missing_names(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $client = Client::factory()->create([
            'user_id' => $user->id,
            'client_type' => 'individual',
            'first_name' => 'Juan',
            'last_name' => null,
        ]);

        $this->assertEquals('Juan', trim($client->full_name));
    }

    public function test_is_company_returns_true_for_company(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $client = Client::factory()->create([
            'user_id' => $user->id,
            'client_type' => 'company',
        ]);

        $this->assertTrue($client->isCompany());
    }

    public function test_is_company_returns_false_for_individual(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $client = Client::factory()->create([
            'user_id' => $user->id,
            'client_type' => 'individual',
        ]);

        $this->assertFalse($client->isCompany());
    }

    public function test_is_individual_returns_true_for_individual(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $client = Client::factory()->create([
            'user_id' => $user->id,
            'client_type' => 'individual',
        ]);

        $this->assertTrue($client->isIndividual());
    }

    public function test_is_individual_returns_false_for_company(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $client = Client::factory()->create([
            'user_id' => $user->id,
            'client_type' => 'company',
        ]);

        $this->assertFalse($client->isIndividual());
    }

    public function test_scope_active_filters_active_clients(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $activeClient = Client::factory()->create([
            'user_id' => $user->id,
            'active' => true,
        ]);

        $inactiveClient = Client::factory()->create([
            'user_id' => $user->id,
            'active' => false,
        ]);

        $results = Client::active()->get();

        $this->assertTrue($results->contains('id', $activeClient->id));
        $this->assertFalse($results->contains('id', $inactiveClient->id));
    }

    public function test_scope_for_user_filters_by_user(): void
    {
        $user1 = User::factory()->create(['role' => 'viticulturist']);
        $user2 = User::factory()->create(['role' => 'viticulturist']);

        $client1 = Client::factory()->create(['user_id' => $user1->id]);
        $client2 = Client::factory()->create(['user_id' => $user2->id]);
        $client3 = Client::factory()->create(['user_id' => $user1->id]);

        $results = Client::forUser($user1->id)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $client1->id));
        $this->assertTrue($results->contains('id', $client3->id));
        $this->assertFalse($results->contains('id', $client2->id));
    }
}
