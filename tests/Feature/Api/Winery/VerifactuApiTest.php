<?php

namespace Tests\Feature\Api\Winery;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests del API móvil de VeriFactu, ya cableado al servicio real
 * (Invoice.sif_* + SifRecord + VerifactuService). En entorno testing el envío a
 * la AEAT está simulado, así que `submit` marca la factura como aceptada y crea
 * el SifRecord correspondiente sin contactar con AEAT.
 *
 * Endpoint base: /api/v1/winery/verifactu
 */
class VerifactuApiTest extends TestCase
{
    use RefreshDatabase;

    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();

        // is_beta_user/abilities_configured se fuerzan explícitamente porque
        // Sanctum::actingAs() reutiliza el modelo en memoria del factory sin
        // recargarlo de BD, así que los defaults de columna (aplicados solo a
        // nivel de fila) no llegan al objeto autenticado durante el request.
        $this->winery = User::factory()->winery()->create([
            'can_login' => true,
            'dni' => '12345678Z',
            'is_beta_user' => true,
            'abilities_configured' => false,
        ]);
    }

    // ─── Autenticación / autorización ──────────────────────────────────────────

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/winery/verifactu')->assertStatus(401);
    }

    public function test_viticulturist_role_is_forbidden(): void
    {
        $vit = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->api($vit)->getJson('/api/v1/winery/verifactu')->assertStatus(403);
    }

    // ─── index ─────────────────────────────────────────────────────────────────

    public function test_index_lists_own_non_draft_invoices_only(): void
    {
        $this->makeInvoice();                                   // propia, visible
        $this->makeInvoice(['status' => 'draft']);              // borrador → oculta
        $other = User::factory()->winery()->create(['can_login' => true, 'dni' => '99999999R', 'is_beta_user' => true]);
        $this->makeInvoice([], $other);                         // de otra bodega → oculta

        $this->api($this->winery)
            ->getJson('/api/v1/winery/verifactu')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.pending_invoices', 1);
    }

    // ─── submit ────────────────────────────────────────────────────────────────

    public function test_submit_declares_invoice_and_creates_sif_record(): void
    {
        $invoice = $this->makeInvoice();

        $this->api($this->winery)
            ->postJson('/api/v1/winery/verifactu/submit', ['invoice_id' => $invoice->id])
            ->assertStatus(200)
            ->assertJsonPath('data.success', true)
            ->assertJsonPath('data.invoice.sif_status', 'aceptado');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'sif_status' => 'aceptado',
            'is_verified_aet' => true,
        ]);
        $this->assertDatabaseHas('sif_records', [
            'invoice_id' => $invoice->id,
            'tipo_registro' => 'ALTA',
            'status' => 'OK',
        ]);
    }

    public function test_submit_rejects_draft_invoice(): void
    {
        $invoice = $this->makeInvoice(['status' => 'draft']);

        $this->api($this->winery)
            ->postJson('/api/v1/winery/verifactu/submit', ['invoice_id' => $invoice->id])
            ->assertStatus(422);
    }

    public function test_submit_rejects_already_accepted_invoice(): void
    {
        $invoice = $this->makeInvoice(['sif_status' => 'aceptado']);

        $this->api($this->winery)
            ->postJson('/api/v1/winery/verifactu/submit', ['invoice_id' => $invoice->id])
            ->assertStatus(422);
    }

    public function test_cannot_submit_other_winery_invoice(): void
    {
        $other = User::factory()->winery()->create(['can_login' => true, 'dni' => '99999999R']);
        $invoice = $this->makeInvoice([], $other);

        $this->api($this->winery)
            ->postJson('/api/v1/winery/verifactu/submit', ['invoice_id' => $invoice->id])
            ->assertStatus(404);
    }

    // ─── cancel ────────────────────────────────────────────────────────────────

    public function test_cancel_requires_accepted_invoice(): void
    {
        $invoice = $this->makeInvoice(['sif_status' => 'pendiente']);

        $this->api($this->winery)
            ->postJson("/api/v1/winery/verifactu/{$invoice->id}/cancel")
            ->assertStatus(422);
    }

    public function test_cancel_accepted_invoice_returns_to_pending(): void
    {
        $invoice = $this->makeInvoice(['sif_status' => 'aceptado', 'is_verified_aet' => true]);

        $this->api($this->winery)
            ->postJson("/api/v1/winery/verifactu/{$invoice->id}/cancel")
            ->assertStatus(200)
            ->assertJsonPath('data.success', true);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'sif_status' => 'pendiente',
            'is_verified_aet' => false,
        ]);
    }

    // ─── helpers ───────────────────────────────────────────────────────────────

    private function api(User $user): self
    {
        Sanctum::actingAs($user, ['*']);

        return $this;
    }

    private function makeInvoice(array $attrs = [], ?User $owner = null): Invoice
    {
        return Invoice::create(array_merge([
            'user_id' => ($owner ?? $this->winery)->id,
            'invoice_number' => 'F-2026-'.fake()->unique()->numerify('####'),
            'invoice_date' => '2026-05-20',
            'tax_base' => 100.00,
            'tax_rate' => 21.00,
            'tax_amount' => 21.00,
            'total_amount' => 121.00,
            'status' => 'sent',
            'sif_status' => 'pendiente',
            'invoice_type' => 'product_sale',
        ], $attrs));
    }
}
