<?php

namespace Tests\Feature\Api\Winery;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de CARACTERIZACIÓN del flujo wine_sale (factura de venta de producto/vino).
 *
 * Congelan el comportamiento ACTUAL de Api\Winery\InvoiceController antes de migrar
 * la autorización a Policies. No introducen comportamiento nuevo.
 *
 * Endpoint base: /api/v1/winery/invoices
 */
class WineSaleInvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery = User::factory()->winery()->create(['can_login' => true]);
    }

    // ─── Autenticación / autorización ──────────────────────────────────────────

    public function test_requires_authentication(): void
    {
        $this->postJson('/api/v1/winery/invoices', [])->assertStatus(401);
    }

    public function test_viticulturist_role_is_forbidden(): void
    {
        $viticulturist = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($viticulturist)
            ->getJson('/api/v1/winery/invoices')
            ->assertStatus(403);
    }

    // ─── store ───────────────────────────────────────────────────────────────

    public function test_store_creates_invoice_with_defaults(): void
    {
        $client = $this->ownClient();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/invoices', [
                'client_id' => $client->id,
                'invoice_date' => '2026-06-01',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->winery->id,
            'client_id' => $client->id,
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'invoice_type' => 'standard',
        ]);
    }

    public function test_store_auto_assigns_invoice_number_when_omitted(): void
    {
        $client = $this->ownClient();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/invoices', [
                'client_id' => $client->id,
                'invoice_date' => '2026-06-01',
            ])
            ->assertStatus(201);

        $this->assertNotEmpty(Invoice::where('user_id', $this->winery->id)->first()->invoice_number);
    }

    public function test_store_computes_total_from_subtotal_and_tax_rate(): void
    {
        $client = $this->ownClient();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/invoices', [
                'client_id' => $client->id,
                'invoice_date' => '2026-06-01',
                'subtotal' => 100,
                'tax_rate' => 21,
            ])
            ->assertStatus(201);

        $invoice = Invoice::where('user_id', $this->winery->id)->first();
        $this->assertEqualsWithDelta(21.0, (float) $invoice->tax_amount, 0.001);
        $this->assertEqualsWithDelta(121.0, (float) $invoice->total_amount, 0.001);
    }

    public function test_store_rejects_foreign_client_with_422(): void
    {
        $otherWinery = User::factory()->winery()->create(['can_login' => true]);
        $foreignClient = $this->ownClient($otherWinery);

        $this->api($this->winery)
            ->postJson('/api/v1/winery/invoices', [
                'client_id' => $foreignClient->id,
                'invoice_date' => '2026-06-01',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['client_id']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/invoices', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['client_id', 'invoice_date']);
    }

    // ─── show / scoping ────────────────────────────────────────────────────────

    public function test_show_returns_own_invoice(): void
    {
        $invoice = $this->makeInvoice();

        $this->api($this->winery)
            ->getJson("/api/v1/winery/invoices/{$invoice->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $invoice->id);
    }

    public function test_show_other_winery_invoice_returns_404(): void
    {
        $otherWinery = User::factory()->winery()->create(['can_login' => true]);
        $invoice = $this->makeInvoice($otherWinery);

        $this->api($this->winery)
            ->getJson("/api/v1/winery/invoices/{$invoice->id}")
            ->assertStatus(404);
    }

    // ─── update ──────────────────────────────────────────────────────────────

    public function test_update_changes_status(): void
    {
        $invoice = $this->makeInvoice();

        $this->api($this->winery)
            ->putJson("/api/v1/winery/invoices/{$invoice->id}", ['status' => 'sent'])
            ->assertStatus(200);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'sent']);
    }

    public function test_update_other_winery_invoice_returns_404(): void
    {
        $otherWinery = User::factory()->winery()->create(['can_login' => true]);
        $invoice = $this->makeInvoice($otherWinery);

        $this->api($this->winery)
            ->putJson("/api/v1/winery/invoices/{$invoice->id}", ['status' => 'sent'])
            ->assertStatus(404);
    }

    // ─── destroy ─────────────────────────────────────────────────────────────

    public function test_destroy_deletes_own_invoice(): void
    {
        $invoice = $this->makeInvoice();

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/invoices/{$invoice->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_destroy_other_winery_invoice_returns_404(): void
    {
        $otherWinery = User::factory()->winery()->create(['can_login' => true]);
        $invoice = $this->makeInvoice($otherWinery);

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/invoices/{$invoice->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    // ─── index ───────────────────────────────────────────────────────────────

    public function test_index_only_returns_own_invoices(): void
    {
        $this->makeInvoice();
        $otherWinery = User::factory()->winery()->create(['can_login' => true]);
        $this->makeInvoice($otherWinery);

        $this->api($this->winery)
            ->getJson('/api/v1/winery/invoices')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    private function api(User $user): self
    {
        Sanctum::actingAs($user, ['*']);

        return $this;
    }

    private function ownClient(?User $winery = null): Client
    {
        return Client::factory()->individual()->create([
            'user_id' => ($winery ?? $this->winery)->id,
            'active' => true,
        ]);
    }

    // ─── helper ──────────────────────────────────────────────────────────────

    private function makeInvoice(?User $winery = null): Invoice
    {
        $winery = $winery ?? $this->winery;
        $client = $this->ownClient($winery);

        return Invoice::create([
            'user_id' => $winery->id,
            'client_id' => $client->id,
            'invoice_number' => 'F-'.uniqid(),
            'invoice_date' => '2026-06-01',
            'invoice_type' => 'standard',
            'status' => 'draft',
            'payment_status' => 'unpaid',
        ]);
    }
}
