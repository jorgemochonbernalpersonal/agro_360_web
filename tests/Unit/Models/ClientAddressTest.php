<?php

namespace Tests\Unit\Models;

use App\Models\ClientAddress;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\AutonomousCommunity;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;

class ClientAddressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }

    public function test_client_address_belongs_to_client(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $address = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección Principal',
            'address' => 'Calle Test 123',
            'postal_code' => '28001',
        ]);

        $this->assertEquals($client->id, $address->client->id);
        $this->assertInstanceOf(Client::class, $address->client);
    }

    public function test_client_address_belongs_to_autonomous_community(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $autonomousCommunity = AutonomousCommunity::first();

        $address = ClientAddress::create([
            'client_id' => $client->id,
            'autonomous_community_id' => $autonomousCommunity->id,
            'name' => 'Dirección Test',
            'address' => 'Calle Test 123',
            'postal_code' => '28001',
        ]);

        $this->assertEquals($autonomousCommunity->id, $address->autonomousCommunity->id);
    }

    public function test_client_address_belongs_to_province(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $province = Province::first();

        $address = ClientAddress::create([
            'client_id' => $client->id,
            'province_id' => $province->id,
            'name' => 'Dirección Test',
            'address' => 'Calle Test 123',
            'postal_code' => '28001',
        ]);

        $this->assertEquals($province->id, $address->province->id);
    }

    public function test_client_address_belongs_to_municipality(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $municipality = Municipality::first();

        $address = ClientAddress::create([
            'client_id' => $client->id,
            'municipality_id' => $municipality->id,
            'name' => 'Dirección Test',
            'address' => 'Calle Test 123',
            'postal_code' => '28001',
        ]);

        $this->assertEquals($municipality->id, $address->municipality->id);
    }

    public function test_client_address_has_many_invoices(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $address = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección Principal',
            'address' => 'Calle Test 123',
            'postal_code' => '28001',
        ]);

        Invoice::factory()->count(3)->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'client_address_id' => $address->id,
        ]);

        $this->assertCount(3, $address->invoices);
        $address->invoices->each(function ($invoice) use ($address) {
            $this->assertEquals($address->id, $invoice->client_address_id);
        });
    }

    public function test_full_address_attribute_formats_correctly(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $municipality = Municipality::first();
        $province = Province::first();

        $address = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección Test',
            'address' => 'Calle Test 123',
            'municipality_id' => $municipality->id,
            'province_id' => $province->id,
            'postal_code' => '28001',
        ]);

        $fullAddress = $address->full_address;

        $this->assertStringContainsString('Calle Test 123', $fullAddress);
        $this->assertStringContainsString($municipality->name, $fullAddress);
        $this->assertStringContainsString($province->name, $fullAddress);
        $this->assertStringContainsString('28001', $fullAddress);
    }

    public function test_full_address_handles_missing_fields(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $address = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección Test',
            'address' => 'Calle Test 123',
            'postal_code' => null,
            'municipality_id' => null,
            'province_id' => null,
        ]);

        $fullAddress = $address->full_address;

        $this->assertStringContainsString('Calle Test 123', $fullAddress);
        $this->assertNotEmpty($fullAddress);
    }

    public function test_full_address_returns_only_address_when_no_location_data(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $address = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección Test',
            'address' => 'Calle Test 123',
            'municipality_id' => null,
            'province_id' => null,
            'postal_code' => null,
        ]);

        $this->assertEquals('Calle Test 123', $address->full_address);
    }

    public function test_scope_default_returns_only_default_addresses(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $defaultAddress1 = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección Principal',
            'address' => 'Calle Test 123',
            'postal_code' => '28001',
            'is_default' => true,
        ]);

        $defaultAddress2 = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Otra Dirección Principal',
            'address' => 'Calle Test 456',
            'postal_code' => '28002',
            'is_default' => true,
        ]);

        $nonDefaultAddress = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección Secundaria',
            'address' => 'Calle Test 789',
            'postal_code' => '28003',
            'is_default' => false,
        ]);

        $defaults = ClientAddress::default()->get();

        $this->assertCount(2, $defaults);
        $defaults->each(function ($address) {
            $this->assertTrue($address->is_default);
        });
        $this->assertTrue($defaults->contains('id', $defaultAddress1->id));
        $this->assertTrue($defaults->contains('id', $defaultAddress2->id));
        $this->assertFalse($defaults->contains('id', $nonDefaultAddress->id));
    }

    public function test_scope_for_delivery_note_returns_only_delivery_addresses(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $deliveryAddress = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección de Entrega',
            'address' => 'Calle Entrega 123',
            'postal_code' => '28001',
            'is_delivery_note_address' => true,
        ]);

        $nonDeliveryAddress = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección Normal',
            'address' => 'Calle Normal 456',
            'postal_code' => '28002',
            'is_delivery_note_address' => false,
        ]);

        $deliveryAddresses = ClientAddress::forDeliveryNote()->get();

        $this->assertCount(1, $deliveryAddresses);
        $this->assertTrue($deliveryAddresses->first()->is_delivery_note_address);
        $this->assertEquals($deliveryAddress->id, $deliveryAddresses->first()->id);
        $this->assertFalse($deliveryAddresses->contains('id', $nonDeliveryAddress->id));
    }

    public function test_is_default_is_cast_to_boolean(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $address = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección Test',
            'address' => 'Calle Test 123',
            'postal_code' => '28001',
            'is_default' => true,
        ]);

        $this->assertIsBool($address->is_default);
        $this->assertTrue($address->is_default);
    }

    public function test_is_delivery_note_address_is_cast_to_boolean(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $address = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección Test',
            'address' => 'Calle Test 123',
            'postal_code' => '28001',
            'is_delivery_note_address' => true,
        ]);

        $this->assertIsBool($address->is_delivery_note_address);
        $this->assertTrue($address->is_delivery_note_address);
    }

    public function test_address_can_have_optional_fields(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $address = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección Mínima',
            'address' => 'Calle Test 123',
            'first_name' => null,
            'last_name' => null,
            'email' => null,
            'phone' => null,
            'position' => null,
            'description' => null,
        ]);

        $this->assertNotNull($address->id);
        $this->assertNull($address->first_name);
        $this->assertNull($address->email);
    }

    public function test_address_can_store_contact_information(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $address = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección con Contacto',
            'address' => 'Calle Test 123',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan.perez@example.com',
            'phone' => '+34 600 123 456',
            'position' => 'Gerente',
            'description' => 'Dirección de facturación',
        ]);

        $this->assertEquals('Juan', $address->first_name);
        $this->assertEquals('Pérez', $address->last_name);
        $this->assertEquals('juan.perez@example.com', $address->email);
        $this->assertEquals('+34 600 123 456', $address->phone);
        $this->assertEquals('Gerente', $address->position);
        $this->assertEquals('Dirección de facturación', $address->description);
    }
}

