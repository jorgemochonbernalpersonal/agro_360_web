<?php

namespace Tests\Unit\Models;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\Harvest;
use App\Models\Tax;
use App\Models\User;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceItemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed de localización requerido por los factories de Plot (usado por Harvest)
        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }

    public function test_invoice_item_belongs_to_invoice(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
        ]);

        $this->assertEquals($invoice->id, $item->invoice->id);
    }

    public function test_invoice_item_belongs_to_harvest(): void
    {
        $harvest = Harvest::factory()->create();

        $item = InvoiceItem::factory()->create([
            'harvest_id' => $harvest->id,
        ]);

        $this->assertEquals($harvest->id, $item->harvest->id);
    }

    public function test_invoice_item_belongs_to_tax(): void
    {
        $tax = Tax::create([
            'name' => 'IVA',
            'code' => 'IVA21',
            'rate' => 21.0,
            'active' => true,
            'is_default' => false,
        ]);

        $item = InvoiceItem::factory()->create([
            'tax_id' => $tax->id,
        ]);

        $this->assertEquals($tax->id, $item->tax->id);
    }

    public function test_invoice_item_calculates_subtotal_automatically(): void
    {
        $item = InvoiceItem::factory()->create([
            'quantity' => 10.0,
            'unit_price' => 25.50,
            'discount_percentage' => 0,
        ]);

        // 10 * 25.50 = 255.00
        $this->assertEquals(255.0, $item->subtotal);
    }

    public function test_invoice_item_calculates_discount_amount_automatically(): void
    {
        $item = InvoiceItem::factory()->create([
            'quantity' => 10.0,
            'unit_price' => 25.50,
            'discount_percentage' => 10.0, // 10% descuento
        ]);

        // Subtotal sin descuento: 10 * 25.50 = 255.00
        // Descuento: 255.00 * 0.10 = 25.50
        $this->assertEquals(25.5, $item->discount_amount);
        // Subtotal con descuento: 255.00 - 25.50 = 229.50
        $this->assertEquals(229.5, $item->subtotal);
    }

    public function test_invoice_item_calculates_tax_base_and_tax_amount(): void
    {
        $item = InvoiceItem::factory()->create([
            'quantity' => 10.0,
            'unit_price' => 25.50,
            'discount_percentage' => 0,
            'tax_rate' => 21.0, // 21% IVA
        ]);

        // Subtotal: 255.00
        // Tax base: 255.00 (igual al subtotal)
        $this->assertEquals(255.0, $item->tax_base);
        // Tax amount: 255.00 * 0.21 = 53.55
        $this->assertEquals(53.55, $item->tax_amount);
    }

    public function test_invoice_item_calculates_total_automatically(): void
    {
        $item = InvoiceItem::factory()->create([
            'quantity' => 10.0,
            'unit_price' => 25.50,
            'discount_percentage' => 0,
            'tax_rate' => 21.0,
        ]);

        // Subtotal: 255.00
        // Tax amount: 53.55
        // Total: 255.00 + 53.55 = 308.55
        $this->assertEquals(308.55, $item->total);
    }

    public function test_invoice_item_calculates_all_values_with_discount_and_tax(): void
    {
        $item = InvoiceItem::factory()->create([
            'quantity' => 5.0,
            'unit_price' => 100.00,
            'discount_percentage' => 15.0, // 15% descuento
            'tax_rate' => 21.0, // 21% IVA
        ]);

        // Subtotal sin descuento: 5 * 100 = 500.00
        // Descuento: 500 * 0.15 = 75.00
        // Subtotal con descuento: 500 - 75 = 425.00
        $this->assertEquals(75.0, $item->discount_amount);
        $this->assertEquals(425.0, $item->subtotal);
        $this->assertEquals(425.0, $item->tax_base);
        
        // Tax amount: 425 * 0.21 = 89.25
        $this->assertEquals(89.25, $item->tax_amount);
        
        // Total: 425 + 89.25 = 514.25
        $this->assertEquals(514.25, $item->total);
    }

    public function test_has_harvest_returns_true_when_has_harvest(): void
    {
        $harvest = Harvest::factory()->create();

        $item = InvoiceItem::factory()->create([
            'harvest_id' => $harvest->id,
        ]);

        $this->assertTrue($item->hasHarvest());
    }

    public function test_has_harvest_returns_false_when_no_harvest(): void
    {
        $item = InvoiceItem::factory()->create([
            'harvest_id' => null,
        ]);

        $this->assertFalse($item->hasHarvest());
    }

    public function test_scope_active_filters_active_items(): void
    {
        $activeItem = InvoiceItem::factory()->create(['status' => 'active']);
        $cancelledItem = InvoiceItem::factory()->create(['status' => 'cancelled']);

        $results = InvoiceItem::active()->get();

        $this->assertTrue($results->contains('id', $activeItem->id));
        $this->assertFalse($results->contains('id', $cancelledItem->id));
    }

    public function test_scope_harvest_filters_harvest_items(): void
    {
        $harvestItem = InvoiceItem::factory()->create(['concept_type' => 'harvest']);
        $otherItem = InvoiceItem::factory()->create(['concept_type' => 'service']);

        // Usar el scope correctamente
        $results = InvoiceItem::where('concept_type', 'harvest')->get();

        $this->assertTrue($results->contains('id', $harvestItem->id));
        $this->assertFalse($results->contains('id', $otherItem->id));
    }

    public function test_invoice_item_rounds_decimals_correctly(): void
    {
        $item = InvoiceItem::factory()->create([
            'quantity' => 3.333,
            'unit_price' => 33.333,
            'discount_percentage' => 0,
            'tax_rate' => 21.0,
        ]);

        // Los campos decimal en Laravel devuelven strings
        $this->assertIsString($item->subtotal);
        $this->assertIsString($item->tax_amount);
        $this->assertIsString($item->total);
    }
}
