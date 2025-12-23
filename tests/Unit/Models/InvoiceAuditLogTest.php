<?php

namespace Tests\Unit\Models;

use App\Models\InvoiceAuditLog;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_audit_log_belongs_to_invoice(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $log = InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'action' => 'created',
            'description' => 'Invoice created',
        ]);

        $this->assertEquals($invoice->id, $log->invoice->id);
        $this->assertInstanceOf(Invoice::class, $log->invoice);
    }

    public function test_invoice_audit_log_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $log = InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'action' => 'updated',
            'description' => 'Invoice updated',
        ]);

        $this->assertEquals($user->id, $log->user->id);
        $this->assertInstanceOf(User::class, $log->user);
    }

    public function test_log_static_method_creates_log_with_auth_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $this->actingAs($user);

        $log = InvoiceAuditLog::log($invoice, 'status_changed', 'Invoice status changed to sent', [
            'old_status' => 'draft',
            'new_status' => 'sent',
        ]);

        $this->assertEquals($invoice->id, $log->invoice_id);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('status_changed', $log->action);
        $this->assertEquals('Invoice status changed to sent', $log->description);
        $this->assertIsArray($log->changes);
        $this->assertEquals('draft', $log->changes['old_status']);
        $this->assertEquals('sent', $log->changes['new_status']);
    }

    public function test_log_static_method_captures_ip_address(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $this->actingAs($user);

        $log = InvoiceAuditLog::log($invoice, 'created', 'Invoice created');

        $this->assertNotNull($log->ip_address);
    }

    public function test_log_static_method_captures_user_agent(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $this->actingAs($user);

        $log = InvoiceAuditLog::log($invoice, 'created', 'Invoice created');

        $this->assertNotNull($log->user_agent);
    }

    public function test_changes_field_is_cast_to_array(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $changes = [
            'old_status' => 'draft',
            'new_status' => 'sent',
            'old_total' => 1000.00,
            'new_total' => 1200.00,
        ];

        $log = InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'action' => 'updated',
            'description' => 'Invoice updated',
            'changes' => $changes,
        ]);

        $this->assertIsArray($log->changes);
        $this->assertEquals('draft', $log->changes['old_status']);
        $this->assertEquals('sent', $log->changes['new_status']);
        $this->assertEquals(1000.00, $log->changes['old_total']);
        $this->assertEquals(1200.00, $log->changes['new_total']);
    }

    public function test_scope_for_invoice_filters_by_invoice(): void
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

        $log1 = InvoiceAuditLog::create([
            'invoice_id' => $invoice1->id,
            'user_id' => $user->id,
            'action' => 'created',
            'description' => 'Invoice 1 created',
        ]);

        $log2 = InvoiceAuditLog::create([
            'invoice_id' => $invoice1->id,
            'user_id' => $user->id,
            'action' => 'updated',
            'description' => 'Invoice 1 updated',
        ]);

        $log3 = InvoiceAuditLog::create([
            'invoice_id' => $invoice2->id,
            'user_id' => $user->id,
            'action' => 'created',
            'description' => 'Invoice 2 created',
        ]);

        $results = InvoiceAuditLog::forInvoice($invoice1->id)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $log1->id));
        $this->assertTrue($results->contains('id', $log2->id));
        $this->assertFalse($results->contains('id', $log3->id));
    }

    public function test_scope_by_user_filters_by_user(): void
    {
        $user1 = User::factory()->create(['role' => 'viticulturist']);
        $user2 = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user1->id]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user1->id,
            'client_id' => $client->id,
        ]);

        $log1 = InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user1->id,
            'action' => 'created',
            'description' => 'Created by user1',
        ]);

        $log2 = InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user2->id,
            'action' => 'updated',
            'description' => 'Updated by user2',
        ]);

        $results = InvoiceAuditLog::byUser($user1->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($log1->id, $results->first()->id);
    }

    public function test_scope_action_filters_by_action(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $log1 = InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'action' => 'created',
            'description' => 'Invoice created',
        ]);

        $log2 = InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'action' => 'updated',
            'description' => 'Invoice updated',
        ]);

        $log3 = InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'action' => 'created',
            'description' => 'Invoice created again',
        ]);

        $results = InvoiceAuditLog::action('created')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $log1->id));
        $this->assertTrue($results->contains('id', $log3->id));
        $this->assertFalse($results->contains('id', $log2->id));
    }

    public function test_log_can_have_empty_changes(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $this->actingAs($user);

        $log = InvoiceAuditLog::log($invoice, 'viewed', 'Invoice viewed', []);

        $this->assertIsArray($log->changes);
        $this->assertEmpty($log->changes);
    }

    public function test_log_can_have_null_changes(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $log = InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'action' => 'viewed',
            'description' => 'Invoice viewed',
            'changes' => null,
        ]);

        $this->assertNull($log->changes);
    }
}

