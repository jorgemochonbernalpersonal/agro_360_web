<?php

namespace Tests\Feature\Api\Winery;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de CARACTERIZACIÓN del flujo grape_purchase (liquidación de compra de uva).
 *
 * Congelan el comportamiento ACTUAL del API (incluyendo los abort_unless/abort_if
 * inline) antes de migrar la autorización a Policies. No introducen comportamiento
 * nuevo: si un test falla tras el refactor, es una regresión.
 *
 * Endpoint base: /api/v1/winery/grape-invoices
 */
class GrapePurchaseInvoiceApiTest extends TestCase
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
        $this->postJson('/api/v1/winery/grape-invoices', [])->assertStatus(401);
    }

    public function test_viticulturist_role_is_forbidden(): void
    {
        $viticulturist = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($viticulturist)
            ->getJson('/api/v1/winery/grape-invoices')
            ->assertStatus(403);
    }

    public function test_producer_with_external_grape_can_access(): void
    {
        $producer = User::factory()->producer()->create(['can_login' => true, 'compra_uva_externa' => true]);

        $this->api($producer)
            ->getJson('/api/v1/winery/grape-invoices')
            ->assertStatus(200);
    }

    public function test_producer_without_external_grape_is_forbidden(): void
    {
        $producer = User::factory()->producer()->create(['can_login' => true, 'compra_uva_externa' => false]);

        $this->api($producer)
            ->getJson('/api/v1/winery/grape-invoices')
            ->assertStatus(403);
    }

    // ─── store ───────────────────────────────────────────────────────────────

    public function test_store_creates_grape_purchase_invoice(): void
    {
        $viticulturist = $this->linkViticulturist();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/grape-invoices', $this->validPayload($viticulturist))
            ->assertStatus(201)
            ->assertJsonStructure(['data', 'message']);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->winery->id,
            'viticulturist_id' => $viticulturist->id,
            'invoice_type' => 'grape_purchase',
            'status' => 'draft',
            'payment_status' => 'unpaid',
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'name' => 'Tempranillo 2026',
            'concept_type' => 'harvest',
        ]);
    }

    public function test_store_computes_totals_from_items(): void
    {
        $viticulturist = $this->linkViticulturist();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/grape-invoices', $this->validPayload($viticulturist))
            ->assertStatus(201);

        // 1000 * 0.85 = 850 subtotal; IVA 21% = 178.5; total = 1028.5
        $invoice = Invoice::where('user_id', $this->winery->id)->first();
        $this->assertEqualsWithDelta(850.0, (float) $invoice->subtotal, 0.001);
        $this->assertEqualsWithDelta(178.5, (float) $invoice->tax_amount, 0.001);
        $this->assertEqualsWithDelta(1028.5, (float) $invoice->total_amount, 0.001);
    }

    public function test_store_auto_assigns_invoice_number_when_omitted(): void
    {
        $viticulturist = $this->linkViticulturist();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/grape-invoices', $this->validPayload($viticulturist))
            ->assertStatus(201);

        $invoice = Invoice::where('user_id', $this->winery->id)->first();
        $this->assertNotEmpty($invoice->invoice_number);
    }

    public function test_store_rejects_unlinked_viticulturist_with_422(): void
    {
        $stranger = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($this->winery)
            ->postJson('/api/v1/winery/grape-invoices', $this->validPayload($stranger))
            ->assertStatus(422);

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_store_requires_at_least_one_item(): void
    {
        $viticulturist = $this->linkViticulturist();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/grape-invoices', $this->validPayload($viticulturist, ['items' => []]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_store_validates_required_viticulturist_and_date(): void
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/grape-invoices', ['items' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['viticulturist_id', 'invoice_date', 'items']);
    }

    // ─── show / scoping ────────────────────────────────────────────────────────

    public function test_show_returns_own_invoice(): void
    {
        $viticulturist = $this->linkViticulturist();
        $invoice = $this->createInvoiceVia($viticulturist);

        $this->api($this->winery)
            ->getJson("/api/v1/winery/grape-invoices/{$invoice->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $invoice->id);
    }

    public function test_show_other_winery_invoice_returns_404(): void
    {
        $otherWinery = User::factory()->winery()->create(['can_login' => true]);
        $viticulturist = $this->linkViticulturist($otherWinery);
        $invoice = Invoice::create([
            'user_id' => $otherWinery->id,
            'viticulturist_id' => $viticulturist->id,
            'invoice_type' => 'grape_purchase',
            'invoice_date' => '2026-06-01',
            'status' => 'draft',
            'payment_status' => 'unpaid',
        ]);

        $this->api($this->winery)
            ->getJson("/api/v1/winery/grape-invoices/{$invoice->id}")
            ->assertStatus(404);
    }

    // ─── update ──────────────────────────────────────────────────────────────

    public function test_update_modifies_observations(): void
    {
        $viticulturist = $this->linkViticulturist();
        $invoice = $this->createInvoiceVia($viticulturist);

        $this->api($this->winery)
            ->putJson("/api/v1/winery/grape-invoices/{$invoice->id}", ['observations' => 'Pago a 30 días'])
            ->assertStatus(200);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'observations' => 'Pago a 30 días',
        ]);
    }

    public function test_update_cancelled_invoice_returns_422(): void
    {
        $viticulturist = $this->linkViticulturist();
        $invoice = $this->createInvoiceVia($viticulturist);
        $invoice->update(['status' => 'cancelled']);

        $this->api($this->winery)
            ->putJson("/api/v1/winery/grape-invoices/{$invoice->id}", ['observations' => 'x'])
            ->assertStatus(422);
    }

    // ─── destroy ─────────────────────────────────────────────────────────────

    public function test_destroy_deletes_invoice_and_items(): void
    {
        $viticulturist = $this->linkViticulturist();
        $invoice = $this->createInvoiceVia($viticulturist);

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/grape-invoices/{$invoice->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('invoice_items', ['invoice_id' => $invoice->id]);
    }

    public function test_destroy_paid_invoice_returns_422(): void
    {
        $viticulturist = $this->linkViticulturist();
        $invoice = $this->createInvoiceVia($viticulturist);
        $invoice->update(['payment_status' => 'paid']);

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/grape-invoices/{$invoice->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    // ─── items ───────────────────────────────────────────────────────────────

    public function test_add_item_recalculates_totals(): void
    {
        $viticulturist = $this->linkViticulturist();
        $invoice = $this->createInvoiceVia($viticulturist);

        $this->api($this->winery)
            ->postJson("/api/v1/winery/grape-invoices/{$invoice->id}/items", [
                'name' => 'Garnacha',
                'quantity' => 100,
                'unit' => 'kg',
                'unit_price' => 1.0,
                'tax_rate' => 0,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'name' => 'Garnacha',
        ]);
    }

    public function test_remove_item(): void
    {
        $viticulturist = $this->linkViticulturist();
        $invoice = $this->createInvoiceVia($viticulturist);
        $item = $invoice->items()->first();

        $this->api($this->winery)
            ->deleteJson("/api/v1/winery/grape-invoices/{$invoice->id}/items/{$item->id}")
            ->assertStatus(200);

        // InvoiceItem usa SoftDeletes: la línea queda con deleted_at, no se borra físicamente.
        $this->assertSoftDeleted('invoice_items', ['id' => $item->id]);
    }

    // ─── confirm / mark-paid (máquina de estados) ───────────────────────────────

    public function test_confirm_moves_draft_to_sent(): void
    {
        $viticulturist = $this->linkViticulturist();
        $invoice = $this->createInvoiceVia($viticulturist);

        $this->api($this->winery)
            ->postJson("/api/v1/winery/grape-invoices/{$invoice->id}/confirm")
            ->assertStatus(200);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'sent']);
    }

    public function test_confirm_non_draft_returns_422(): void
    {
        $viticulturist = $this->linkViticulturist();
        $invoice = $this->createInvoiceVia($viticulturist);
        $invoice->update(['status' => 'sent']);

        $this->api($this->winery)
            ->postJson("/api/v1/winery/grape-invoices/{$invoice->id}/confirm")
            ->assertStatus(422);
    }

    public function test_mark_paid_sets_payment_status(): void
    {
        $viticulturist = $this->linkViticulturist();
        $invoice = $this->createInvoiceVia($viticulturist);

        $this->api($this->winery)
            ->postJson("/api/v1/winery/grape-invoices/{$invoice->id}/mark-paid")
            ->assertStatus(200);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'payment_status' => 'paid']);
    }

    public function test_mark_paid_cancelled_returns_422(): void
    {
        $viticulturist = $this->linkViticulturist();
        $invoice = $this->createInvoiceVia($viticulturist);
        $invoice->update(['status' => 'cancelled']);

        $this->api($this->winery)
            ->postJson("/api/v1/winery/grape-invoices/{$invoice->id}/mark-paid")
            ->assertStatus(422);
    }

    // ─── index ───────────────────────────────────────────────────────────────

    public function test_index_only_returns_own_grape_purchase_invoices(): void
    {
        $viticulturist = $this->linkViticulturist();
        $this->createInvoiceVia($viticulturist);

        // Otra bodega con su propia liquidación
        $otherWinery = User::factory()->winery()->create(['can_login' => true]);
        Invoice::create([
            'user_id' => $otherWinery->id,
            'viticulturist_id' => $viticulturist->id,
            'invoice_type' => 'grape_purchase',
            'invoice_date' => '2026-06-01',
            'status' => 'draft',
            'payment_status' => 'unpaid',
        ]);

        $this->api($this->winery)
            ->getJson('/api/v1/winery/grape-invoices')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    /**
     * Autentica vía Sanctum con un token de abilities totales.
     *
     * ApiRole exige tokenCan($role) además del rol del usuario, así que un
     * actingAs sin token no bastaría (devolvería 403). Sanctum::actingAs con
     * ['*'] adjunta un TransientToken cuyo can() es siempre true.
     */
    private function api(User $user): self
    {
        Sanctum::actingAs($user, ['*']);

        return $this;
    }

    /** Vincula un viticultor a la bodega autenticada (source = own). */
    private function linkViticulturist(?User $winery = null): User
    {
        $winery = $winery ?? $this->winery;
        $viticulturist = User::factory()->viticulturist()->create(['can_login' => true]);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by' => $winery->id,
            'source' => WineryViticulturist::SOURCE_OWN,
        ]);

        return $viticulturist;
    }

    private function validPayload(User $viticulturist, array $overrides = []): array
    {
        return array_merge([
            'viticulturist_id' => $viticulturist->id,
            'invoice_date' => '2026-06-01',
            'items' => [[
                'name' => 'Tempranillo 2026',
                'quantity' => 1000,
                'unit' => 'kg',
                'unit_price' => 0.85,
                'tax_rate' => 21,
            ]],
        ], $overrides);
    }

    // ─── helper ──────────────────────────────────────────────────────────────

    private function createInvoiceVia(User $viticulturist): Invoice
    {
        $this->api($this->winery)
            ->postJson('/api/v1/winery/grape-invoices', $this->validPayload($viticulturist))
            ->assertStatus(201);

        return Invoice::where('user_id', $this->winery->id)->latest('id')->firstOrFail();
    }
}
