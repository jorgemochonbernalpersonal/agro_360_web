<?php

namespace Tests\Feature\Viticulturist\FinancialStats;

use App\Livewire\Viticulturist\FinancialStats;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class FinancialStatsTest extends ViticulturistTestCase
{
    private User $viticulturist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viticulturist = $this->makeViticulturist();
        $this->actingAs($this->viticulturist);
    }

    public function test_renders_for_viticulturist(): void
    {
        Livewire::test(FinancialStats::class)
            ->assertStatus(200);
    }

    public function test_total_invoiced_counts_only_own_invoices(): void
    {
        $this->makeInvoice($this->viticulturist, [
            'total_amount' => 1000,
            'status' => 'sent',
        ]);

        $other = $this->makeOtherViticulturist();
        $this->makeInvoice($other, [
            'total_amount' => 5000,
            'status' => 'sent',
        ]);

        Livewire::test(FinancialStats::class)
            ->assertViewHas('totalInvoiced', fn ($value) => (float) $value === 1000.0);
    }

    public function test_cancelled_invoices_are_excluded_from_total(): void
    {
        $this->makeInvoice($this->viticulturist, [
            'total_amount' => 800,
            'status' => 'sent',
        ]);
        $this->makeInvoice($this->viticulturist, [
            'total_amount' => 999,
            'status' => 'cancelled',
        ]);

        Livewire::test(FinancialStats::class)
            ->assertViewHas('totalInvoiced', fn ($value) => (float) $value === 800.0);
    }

    public function test_paid_invoices_drive_collection_rate(): void
    {
        $this->makeInvoice($this->viticulturist, [
            'total_amount' => 1000,
            'status' => 'sent',
            'payment_status' => 'paid',
        ]);
        $this->makeInvoice($this->viticulturist, [
            'total_amount' => 1000,
            'status' => 'sent',
            'payment_status' => 'unpaid',
        ]);

        Livewire::test(FinancialStats::class)
            ->assertViewHas('collectionRate', fn ($value) => round((float) $value) === 50.0);
    }

    private function makeInvoice(User $owner, array $attrs = []): Invoice
    {
        $client = Client::create([
            'user_id' => $owner->id,
            'client_type' => 'individual',
            'first_name' => 'Cliente',
            'last_name' => 'Test',
            'active' => true,
        ]);

        return Invoice::create(array_merge([
            'user_id' => $owner->id,
            'client_id' => $client->id,
            'invoice_type' => 'wine_sale',
            'invoice_date' => now()->toDateString(),
            'order_date' => now()->toDateString(),
            'status' => 'sent',
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending',
            'subtotal' => 250,
            'discount_amount' => 0,
            'tax_base' => 250,
            'tax_amount' => 52.5,
            'total_amount' => 302.5,
        ], $attrs));
    }
}
