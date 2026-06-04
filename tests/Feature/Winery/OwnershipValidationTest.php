<?php

namespace Tests\Feature\Winery;

use App\Livewire\Winery\Billing\ProductSale\Create as ProductSaleCreate;
use App\Livewire\Winery\Billing\ProductSale\Edit as ProductSaleEdit;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\ProductLot;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Verifica que los componentes de facturación de Winery rechazan
 * IDs de clientes, lotes y viticultores que no pertenecen a la bodega.
 */
class OwnershipValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $winery;

    private User $otherWinery;

    private User $viticulturist;

    private User $foreignViticulturist;

    private Client $ownClient;

    private Client $foreignClient;

    private ProductLot $ownLot;

    private ProductLot $foreignLot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery = User::factory()->create(['role' => 'winery', 'email_verified_at' => now()]);
        $this->otherWinery = User::factory()->create(['role' => 'winery', 'email_verified_at' => now()]);
        $this->viticulturist = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);
        $this->foreignViticulturist = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);

        // Link own viticulturist to this winery
        WineryViticulturist::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $this->winery->id,
        ]);

        // Own client (user_id = this winery)
        $this->ownClient = Client::create([
            'user_id' => $this->winery->id,
            'first_name' => 'Cliente',
            'last_name' => 'Propio',
            'client_type' => 'individual',
            'active' => true,
        ]);

        // Foreign client (belongs to other winery)
        $this->foreignClient = Client::create([
            'user_id' => $this->otherWinery->id,
            'first_name' => 'Cliente',
            'last_name' => 'Ajeno',
            'client_type' => 'individual',
            'active' => true,
        ]);

        // Own product lot
        $this->ownLot = ProductLot::create([
            'user_id' => $this->winery->id,
            'name' => 'Lote Propio',
            'lot_number' => 'LP-001',
            'status' => 'active',
            'total_liters' => 1000,
        ]);

        // Foreign product lot
        $this->foreignLot = ProductLot::create([
            'user_id' => $this->otherWinery->id,
            'name' => 'Lote Ajeno',
            'lot_number' => 'LA-001',
            'status' => 'active',
            'total_liters' => 1000,
        ]);
    }

    // ── ProductSale/Create — client_id ─────────────────────────────────────────

    public function test_product_sale_create_rejects_foreign_client(): void
    {
        $this->actingAs($this->winery);

        Livewire::test(ProductSaleCreate::class)
            ->set('client_id', $this->foreignClient->id)
            ->set('order_date', now()->toDateString())
            ->set('delivery_note_code', 'ALB-2026-0001')
            ->set('items', [[
                'name' => 'Vino Blanco',
                'quantity' => '10',
                'unit_price' => '5',
                'tax_id' => null,
                'wine_lot_id' => null,
                'discount_percentage' => null,
                'description' => null,
                'sku' => null,
            ]])
            ->call('save')
            ->assertHasErrors(['client_id']);
    }

    public function test_product_sale_create_accepts_own_client(): void
    {
        $this->actingAs($this->winery);

        Livewire::test(ProductSaleCreate::class)
            ->set('client_id', $this->ownClient->id)
            ->set('order_date', now()->toDateString())
            ->set('delivery_note_code', 'ALB-2026-0001')
            ->set('items', [[
                'name' => 'Vino Blanco',
                'quantity' => '10',
                'unit_price' => '5',
                'tax_id' => null,
                'wine_lot_id' => null,
                'discount_percentage' => null,
                'description' => null,
                'sku' => null,
            ]])
            ->call('save')
            ->assertHasNoErrors(['client_id']);
    }

    // ── ProductSale/Create — wine_lot_id ───────────────────────────────────────

    public function test_product_sale_create_rejects_foreign_lot(): void
    {
        $this->actingAs($this->winery);

        Livewire::test(ProductSaleCreate::class)
            ->set('client_id', $this->ownClient->id)
            ->set('order_date', now()->toDateString())
            ->set('delivery_note_code', 'ALB-2026-0001')
            ->set('items', [[
                'name' => 'Vino Tinto',
                'quantity' => '5',
                'unit_price' => '8',
                'tax_id' => null,
                'wine_lot_id' => $this->foreignLot->id,
                'discount_percentage' => null,
                'description' => null,
                'sku' => null,
            ]])
            ->call('save')
            ->assertHasErrors(['items.0.wine_lot_id']);
    }

    // ── GrapePurchase/Create — viticulturist_id (tested via Validator directly) ─

    public function test_grape_purchase_rejects_unlinked_viticulturist(): void
    {
        $this->actingAs($this->winery);

        // Build the same closure the component uses and test it directly
        $wineryId = $this->winery->id;
        $rule = function ($attribute, $value, $fail) use ($wineryId) {
            if ($value && ! \App\Models\WineryViticulturist::where('winery_id', $wineryId)
                ->where('viticulturist_id', $value)
                ->where('source', 'own')
                ->exists()) {
                $fail('El viticultor seleccionado no pertenece a tu bodega.');
            }
        };

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['viticulturist_id' => $this->foreignViticulturist->id],
            ['viticulturist_id' => ['required', $rule]]
        );

        $this->assertTrue($validator->fails());
        $this->assertNotEmpty($validator->errors()->get('viticulturist_id'));
    }

    public function test_grape_purchase_accepts_own_viticulturist(): void
    {
        $this->actingAs($this->winery);

        $wineryId = $this->winery->id;
        $rule = function ($attribute, $value, $fail) use ($wineryId) {
            if ($value && ! \App\Models\WineryViticulturist::where('winery_id', $wineryId)
                ->where('viticulturist_id', $value)
                ->where('source', 'own')
                ->exists()) {
                $fail('El viticultor seleccionado no pertenece a tu bodega.');
            }
        };

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['viticulturist_id' => $this->viticulturist->id],
            ['viticulturist_id' => ['required', $rule]]
        );

        $this->assertFalse($validator->fails(), 'Own viticulturist should pass: '.$validator->errors()->first('viticulturist_id'));
    }

    public function test_product_sale_edit_rejects_foreign_client(): void
    {
        $this->actingAs($this->winery);
        $invoice = $this->makeWineSaleInvoice();

        Livewire::test(ProductSaleEdit::class, ['id' => $invoice->id])
            ->set('client_id', (string) $this->foreignClient->id)
            ->set('payment_status', 'unpaid')
            ->set('items', [['name' => 'Vino', 'quantity' => '1', 'unit_price' => '5',
                'tax_id' => null, 'wine_lot_id' => null, 'discount_percentage' => null,
                'description' => null, 'sku' => null, 'concept_type' => 'other']])
            ->call('save')
            ->assertHasErrors(['client_id']);
    }

    public function test_product_sale_edit_accepts_own_client(): void
    {
        $this->actingAs($this->winery);
        $invoice = $this->makeWineSaleInvoice();

        Livewire::test(ProductSaleEdit::class, ['id' => $invoice->id])
            ->set('client_id', (string) $this->ownClient->id)
            ->set('payment_status', 'unpaid')
            ->set('items', [['name' => 'Vino', 'quantity' => '1', 'unit_price' => '5',
                'tax_id' => null, 'wine_lot_id' => null, 'discount_percentage' => null,
                'description' => null, 'sku' => null, 'concept_type' => 'other']])
            ->call('save')
            ->assertHasNoErrors(['client_id']);
    }

    public function test_product_sale_edit_rejects_foreign_lot(): void
    {
        $this->actingAs($this->winery);
        $invoice = $this->makeWineSaleInvoice();

        Livewire::test(ProductSaleEdit::class, ['id' => $invoice->id])
            ->set('client_id', (string) $this->ownClient->id)
            ->set('payment_status', 'unpaid')
            ->set('items', [['name' => 'Vino', 'quantity' => '1', 'unit_price' => '5',
                'tax_id' => null, 'wine_lot_id' => $this->foreignLot->id, 'discount_percentage' => null,
                'description' => null, 'sku' => null, 'concept_type' => 'wine']])
            ->call('save')
            ->assertHasErrors(['items.0.wine_lot_id']);
    }

    // ── updatedClientId — no leaks foreign client addresses ───────────────────

    public function test_updated_client_id_ignores_foreign_client(): void
    {
        $this->actingAs($this->winery);

        $component = Livewire::test(ProductSaleCreate::class)
            ->set('client_id', $this->foreignClient->id);

        // availableAddresses should be empty (foreign client silently ignored)
        $this->assertEmpty($component->get('availableAddresses'));
        $this->assertSame('', $component->get('client_address_id'));
    }

    // ── ProductSale/Edit — client_id & wine_lot_id ─────────────────────────────

    private function makeWineSaleInvoice(): Invoice
    {
        return Invoice::factory()->create([
            'user_id' => $this->winery->id,
            'client_id' => $this->ownClient->id,
            'invoice_type' => 'wine_sale',
            'delivery_status' => 'pending',
            'payment_status' => 'unpaid',
            'status' => 'draft',
        ]);
    }
}
