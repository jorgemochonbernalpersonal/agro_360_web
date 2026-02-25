<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillingSnapshotTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Traits\CreatesTestHarvest;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_billing_snapshot_populated_on_creation_for_individual_client()
    {
        $client = Client::factory()->individual()->create([
            'user_id'   => $this->user->id,
            'first_name' => 'Juan',
            'last_name'  => 'García',
            'email'      => 'juan@example.com',
            'phone'      => '600123456',
        ]);

        $invoice = Invoice::factory()->draft()->create([
            'user_id'   => $this->user->id,
            'client_id' => $client->id,
        ]);

        $invoice->refresh();

        $this->assertEquals('Juan',             $invoice->billing_first_name);
        $this->assertEquals('García',           $invoice->billing_last_name);
        $this->assertEquals('juan@example.com', $invoice->billing_email);
        $this->assertEquals('600123456',        $invoice->billing_phone);
        $this->assertEquals('España',           $invoice->billing_country);
        // Individual client: company_name and company_document are null
        $this->assertNull($invoice->billing_company_name);
    }

    public function test_billing_snapshot_populated_on_creation_for_company_client()
    {
        $client = Client::factory()->company()->create([
            'user_id'          => $this->user->id,
            'company_name'     => 'Bodegas Ejemplo S.L.',
            'company_document' => 'B12345678',
            'email'            => 'info@bodegas.com',
            'phone'            => '910000000',
        ]);

        $invoice = Invoice::factory()->draft()->create([
            'user_id'   => $this->user->id,
            'client_id' => $client->id,
        ]);

        $invoice->refresh();

        $this->assertEquals('Bodegas Ejemplo S.L.', $invoice->billing_company_name);
        $this->assertEquals('B12345678',            $invoice->billing_company_document);
        $this->assertEquals('info@bodegas.com',     $invoice->billing_email);
        $this->assertEquals('España',               $invoice->billing_country);
    }

    public function test_billing_snapshot_uses_client_address_when_provided()
    {
        $client = Client::factory()->individual()->create([
            'user_id'    => $this->user->id,
            'first_name' => 'Juan',
            'last_name'  => 'García',
            'email'      => 'juan@example.com',
        ]);

        // Create an address for the client (no province/municipality to avoid seeding)
        $address = ClientAddress::create([
            'client_id'   => $client->id,
            'first_name'  => 'Juan',
            'last_name'   => 'García',
            'address'     => 'Calle Mayor 1',
            'postal_code' => '28001',
            'is_default'  => true,
        ]);

        $invoice = Invoice::factory()->draft()->create([
            'user_id'           => $this->user->id,
            'client_id'         => $client->id,
            'client_address_id' => $address->id,
        ]);

        $invoice->refresh();

        $this->assertEquals('Calle Mayor 1', $invoice->billing_address);
        $this->assertEquals('28001',         $invoice->billing_postal_code);
        $this->assertEquals('España',        $invoice->billing_country);
    }

    public function test_billing_snapshot_falls_back_to_default_address()
    {
        $client = Client::factory()->individual()->create([
            'user_id' => $this->user->id,
        ]);

        $defaultAddress = ClientAddress::create([
            'client_id'   => $client->id,
            'address'     => 'Avenida Principal 5',
            'postal_code' => '46001',
            'is_default'  => true,
        ]);

        // Invoice without explicit client_address_id — should use default address
        $invoice = Invoice::factory()->draft()->create([
            'user_id'   => $this->user->id,
            'client_id' => $client->id,
        ]);

        $invoice->refresh();

        $this->assertEquals('Avenida Principal 5', $invoice->billing_address);
        $this->assertEquals('46001',               $invoice->billing_postal_code);
    }

    public function test_billing_snapshot_updated_on_draft_to_sent_transition()
    {
        $client = Client::factory()->individual()->create([
            'user_id'    => $this->user->id,
            'first_name' => 'Pedro',
            'last_name'  => 'Martínez',
            'email'      => 'pedro@example.com',
        ]);

        $invoice = Invoice::factory()->draft()->create([
            'user_id'   => $this->user->id,
            'client_id' => $client->id,
        ]);

        // Change client email after invoice creation
        $client->update(['email' => 'pedro.nuevo@example.com']);

        // Send invoice → snapshot should capture updated email
        $invoice->update(['status' => 'sent']);

        $invoice->refresh();

        $this->assertEquals('sent',                    $invoice->status);
        $this->assertEquals('pedro.nuevo@example.com', $invoice->billing_email);
    }

    public function test_billing_snapshot_works_without_address()
    {
        $client = Client::factory()->individual()->create([
            'user_id'    => $this->user->id,
            'first_name' => 'Ana',
            'last_name'  => 'López',
            'email'      => 'ana@example.com',
        ]);

        // No addresses — should use client fields directly
        $invoice = Invoice::factory()->draft()->create([
            'user_id'   => $this->user->id,
            'client_id' => $client->id,
        ]);

        $invoice->refresh();

        $this->assertEquals('Ana',             $invoice->billing_first_name);
        $this->assertEquals('López',           $invoice->billing_last_name);
        $this->assertEquals('ana@example.com', $invoice->billing_email);
        $this->assertNull($invoice->billing_address);
        $this->assertNull($invoice->billing_postal_code);
    }
}
