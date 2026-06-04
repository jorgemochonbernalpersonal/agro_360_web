<?php

namespace Tests\Feature\Api\Viticulturist;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de CARACTERIZACIÓN del listado de facturas de venta de cosecha (viticultor).
 *
 * Congelan el comportamiento ACTUAL de Api\Viticulturist\HarvestSaleInvoiceController
 * (read-only) antes de migrar la autorización a Policies.
 *
 * Endpoint: GET /api/v1/viticulturist/harvest-sale-invoices
 */
class HarvestSaleInvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    private User $viticulturist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->viticulturist = User::factory()->viticulturist()->create(['can_login' => true]);
    }

    // ─── Autenticación / autorización ──────────────────────────────────────────

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/viticulturist/harvest-sale-invoices')->assertStatus(401);
    }

    public function test_winery_role_is_forbidden(): void
    {
        $winery = User::factory()->winery()->create(['can_login' => true]);

        $this->api($winery)
            ->getJson('/api/v1/viticulturist/harvest-sale-invoices')
            ->assertStatus(403);
    }

    public function test_producer_can_access(): void
    {
        $producer = User::factory()->producer()->create(['can_login' => true]);

        $this->api($producer)
            ->getJson('/api/v1/viticulturist/harvest-sale-invoices')
            ->assertStatus(200);
    }

    // ─── index ───────────────────────────────────────────────────────────────

    public function test_index_returns_own_harvest_sale_invoices(): void
    {
        $this->makeHarvestSaleInvoice($this->viticulturist);
        $this->makeHarvestSaleInvoice($this->viticulturist);

        $this->api($this->viticulturist)
            ->getJson('/api/v1/viticulturist/harvest-sale-invoices')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_excludes_other_invoice_types(): void
    {
        $this->makeHarvestSaleInvoice($this->viticulturist);
        // Una factura de otro tipo NO debe aparecer en este listado.
        $this->makeHarvestSaleInvoice($this->viticulturist, ['invoice_type' => 'standard']);

        $this->api($this->viticulturist)
            ->getJson('/api/v1/viticulturist/harvest-sale-invoices')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_index_excludes_other_users_invoices(): void
    {
        $this->makeHarvestSaleInvoice($this->viticulturist);
        $other = User::factory()->viticulturist()->create(['can_login' => true]);
        $this->makeHarvestSaleInvoice($other);

        $this->api($this->viticulturist)
            ->getJson('/api/v1/viticulturist/harvest-sale-invoices')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_index_filters_by_status(): void
    {
        $this->makeHarvestSaleInvoice($this->viticulturist, ['status' => 'draft']);
        $this->makeHarvestSaleInvoice($this->viticulturist, ['status' => 'sent']);

        $this->api($this->viticulturist)
            ->getJson('/api/v1/viticulturist/harvest-sale-invoices?status=sent')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_index_validates_status_enum(): void
    {
        $this->api($this->viticulturist)
            ->getJson('/api/v1/viticulturist/harvest-sale-invoices?status=bogus')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    private function api(User $user): self
    {
        Sanctum::actingAs($user, ['*']);

        return $this;
    }

    private function makeHarvestSaleInvoice(User $owner, array $overrides = []): Invoice
    {
        return Invoice::create(array_merge([
            'user_id' => $owner->id,
            'invoice_number' => 'V-'.uniqid(),
            'invoice_date' => '2026-06-01',
            'invoice_type' => 'harvest_sale',
            'status' => 'draft',
            'payment_status' => 'unpaid',
        ], $overrides));
    }
}
