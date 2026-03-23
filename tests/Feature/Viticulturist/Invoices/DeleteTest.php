<?php

namespace Tests\Feature\Viticulturist\Invoices;

use App\Livewire\Viticulturist\Invoices\Index;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for Viticulturist Invoice delete functionality.
 *
 * Only draft invoices may be hard-deleted.
 * Sent invoices must go through a corrective instead.
 */
class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private function makeViticulturist(): User
    {
        return User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);
    }

    private function makeInvoice(int $userId, string $status = 'draft'): Invoice
    {
        $client = Client::create([
            'user_id'     => $userId,
            'client_type' => 'individual',
            'first_name'  => 'Cliente',
            'last_name'   => 'Test',
            'active'      => true,
        ]);

        return Invoice::create([
            'user_id'        => $userId,
            'client_id'      => $client->id,
            'status'         => $status,
            'payment_status' => 'unpaid',
            'delivery_status'=> 'pending',
            'subtotal'       => 100,
            'tax_base'       => 100,
            'tax_amount'     => 10,
            'total_amount'   => 110,
        ]);
    }

    // ── happy path ────────────────────────────────────────────────────────────

    public function test_can_delete_draft_invoice(): void
    {
        $viticulturist = $this->makeViticulturist();
        $invoice       = $this->makeInvoice($viticulturist->id, 'draft');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $invoice->id);

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    // ── blocked for non-draft ──────────────────────────────────────────────────

    public function test_cannot_delete_sent_invoice(): void
    {
        $viticulturist = $this->makeViticulturist();
        $invoice       = $this->makeInvoice($viticulturist->id, 'sent');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $invoice->id);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    public function test_cannot_delete_cancelled_invoice(): void
    {
        $viticulturist = $this->makeViticulturist();
        $invoice       = $this->makeInvoice($viticulturist->id, 'cancelled');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $invoice->id);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    // ── authorization ─────────────────────────────────────────────────────────

    public function test_cannot_delete_another_users_invoice(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeViticulturist();
        $invoice       = $this->makeInvoice($other->id, 'draft');

        $this->actingAs($viticulturist);

        // findInvoice returns null for other user's invoice → no deletion
        Livewire::test(Index::class)
            ->call('delete', $invoice->id);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }
}
