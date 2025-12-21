<?php

namespace Tests\Unit\Models;

use App\Models\Invoice;
use App\Models\InvoiceGroup;
use App\Models\User;
use App\Models\Client;
use App\Models\ClientAddress;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_invoice_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals($user->id, $invoice->user->id);
    }

    public function test_invoice_belongs_to_client(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $this->assertEquals($client->id, $invoice->client->id);
    }

    public function test_invoice_has_many_items(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);

        $invoice->items()->create([
            'description' => 'Item 1',
            'quantity' => 1,
            'unit_price' => 100.00,
            'total' => 100.00,
        ]);

        $invoice->items()->create([
            'description' => 'Item 2',
            'quantity' => 2,
            'unit_price' => 50.00,
            'total' => 100.00,
        ]);

        $this->assertCount(2, $invoice->items);
    }

    public function test_is_paid_returns_true_when_payment_status_is_paid(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'paid',
        ]);

        $this->assertTrue($invoice->isPaid());
    }

    public function test_is_paid_returns_false_when_payment_status_is_not_paid(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);

        $this->assertFalse($invoice->isPaid());
    }

    public function test_is_overdue_returns_true_when_due_date_passed_and_not_paid(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'due_date' => Carbon::now()->subDays(5),
            'payment_status' => 'pending',
        ]);

        $this->assertTrue($invoice->isOverdue());
    }

    public function test_is_overdue_returns_false_when_paid(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'due_date' => Carbon::now()->subDays(5),
            'payment_status' => 'paid',
        ]);

        $this->assertFalse($invoice->isOverdue());
    }

    public function test_is_overdue_returns_false_when_due_date_not_passed(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'due_date' => Carbon::now()->addDays(5),
            'payment_status' => 'pending',
        ]);

        $this->assertFalse($invoice->isOverdue());
    }

    public function test_is_draft_returns_true_when_status_is_draft(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $this->assertTrue($invoice->isDraft());
    }

    public function test_is_draft_returns_false_when_status_is_not_draft(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'status' => 'sent',
        ]);

        $this->assertFalse($invoice->isDraft());
    }

    public function test_scope_for_user_filters_correctly(): void
    {
        $user1 = User::factory()->create(['role' => 'viticulturist']);
        $user2 = User::factory()->create(['role' => 'viticulturist']);

        $invoice1 = Invoice::factory()->create(['user_id' => $user1->id]);
        $invoice2 = Invoice::factory()->create(['user_id' => $user2->id]);
        $invoice3 = Invoice::factory()->create(['user_id' => $user1->id]);

        $results = Invoice::forUser($user1->id)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $invoice1->id));
        $this->assertTrue($results->contains('id', $invoice3->id));
        $this->assertFalse($results->contains('id', $invoice2->id));
    }

    public function test_scope_paid_filters_paid_invoices(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $paidInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'paid',
        ]);

        $pendingInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);

        $results = Invoice::paid()->get();

        $this->assertTrue($results->contains('id', $paidInvoice->id));
        $this->assertFalse($results->contains('id', $pendingInvoice->id));
    }

    public function test_scope_unpaid_filters_unpaid_invoices(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $paidInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'paid',
        ]);

        $pendingInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);

        $results = Invoice::unpaid()->get();

        $this->assertFalse($results->contains('id', $paidInvoice->id));
        $this->assertTrue($results->contains('id', $pendingInvoice->id));
    }

    public function test_scope_overdue_filters_overdue_invoices(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $overdueInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'due_date' => Carbon::now()->subDays(5),
            'payment_status' => 'pending',
        ]);

        $notOverdueInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'due_date' => Carbon::now()->addDays(5),
            'payment_status' => 'pending',
        ]);

        $paidInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'due_date' => Carbon::now()->subDays(5),
            'payment_status' => 'paid',
        ]);

        $results = Invoice::overdue()->get();

        $this->assertTrue($results->contains('id', $overdueInvoice->id));
        $this->assertFalse($results->contains('id', $notOverdueInvoice->id));
        $this->assertFalse($results->contains('id', $paidInvoice->id));
    }

    public function test_invoice_belongs_to_client_address(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $clientAddress = ClientAddress::create([
            'client_id' => $client->id,
            'name' => 'Dirección Principal',
            'address' => 'Calle Test 123',
            'postal_code' => '28001',
            'is_default' => true,
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'client_address_id' => $clientAddress->id,
        ]);

        $this->assertEquals($clientAddress->id, $invoice->clientAddress->id);
    }

    public function test_invoice_belongs_to_invoice_group(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoiceGroup = InvoiceGroup::create([
            'user_id' => $user->id,
            'name' => 'Grupo de Facturas 2025',
            'description' => 'Facturas del año 2025',
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_group_id' => $invoiceGroup->id,
        ]);

        $this->assertEquals($invoiceGroup->id, $invoice->invoiceGroup->id);
    }

    public function test_invoice_can_have_null_client_address(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'client_address_id' => null,
        ]);

        $this->assertNull($invoice->clientAddress);
    }

    public function test_invoice_can_have_null_invoice_group(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_group_id' => null,
        ]);

        $this->assertNull($invoice->invoiceGroup);
    }

    public function test_invoice_date_fields_are_cast_to_date(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
        ]);

        $this->assertInstanceOf(Carbon::class, $invoice->invoice_date);
        $this->assertInstanceOf(Carbon::class, $invoice->due_date);
    }

    public function test_invoice_datetime_fields_are_cast_to_datetime(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'delivery_note_date' => now(),
            'payment_date' => now(),
            'order_date' => now(),
        ]);

        $this->assertInstanceOf(Carbon::class, $invoice->delivery_note_date);
        $this->assertInstanceOf(Carbon::class, $invoice->payment_date);
        $this->assertInstanceOf(Carbon::class, $invoice->order_date);
    }

    public function test_invoice_amount_fields_are_cast_to_decimal(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'subtotal' => 1000.50,
            'discount_amount' => 50.25,
            'tax_base' => 950.25,
            'tax_rate' => 21.00,
            'tax_amount' => 199.55,
            'total_amount' => 1149.80,
        ]);

        $this->assertIsFloat($invoice->subtotal);
        $this->assertIsFloat($invoice->discount_amount);
        $this->assertIsFloat($invoice->tax_base);
        $this->assertIsFloat($invoice->tax_rate);
        $this->assertIsFloat($invoice->tax_amount);
        $this->assertIsFloat($invoice->total_amount);
    }

    public function test_invoice_boolean_fields_are_cast_to_boolean(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'bank_payment_status' => true,
            'sif_excluded' => false,
            'is_verified_aet' => true,
            'sent' => false,
            'viewed' => true,
            'delivery_viewed' => false,
            'payment_status_viewed' => true,
            'corrective' => false,
            'gift' => true,
        ]);

        $this->assertIsBool($invoice->bank_payment_status);
        $this->assertIsBool($invoice->sif_excluded);
        $this->assertIsBool($invoice->is_verified_aet);
        $this->assertIsBool($invoice->sent);
        $this->assertIsBool($invoice->viewed);
        $this->assertIsBool($invoice->delivery_viewed);
        $this->assertIsBool($invoice->payment_status_viewed);
        $this->assertIsBool($invoice->corrective);
        $this->assertIsBool($invoice->gift);
    }
}

