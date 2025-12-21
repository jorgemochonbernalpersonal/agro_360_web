<?php

namespace Tests\Unit\Models;

use App\Models\Tax;
use App\Models\User;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_tax_belongs_to_many_users(): void
    {
        $tax = Tax::create([
            'name' => 'IVA',
            'code' => 'IVA',
            'rate' => 21.00,
            'region' => 'spain',
            'active' => true,
        ]);

        $user1 = User::factory()->create(['role' => 'viticulturist']);
        $user2 = User::factory()->create(['role' => 'viticulturist']);

        $tax->users()->attach($user1->id, ['is_default' => true, 'order' => 1]);
        $tax->users()->attach($user2->id, ['is_default' => false, 'order' => 2]);

        $this->assertCount(2, $tax->users);
        $this->assertTrue($tax->users->contains('id', $user1->id));
        $this->assertTrue($tax->users->contains('id', $user2->id));
    }

    public function test_tax_has_many_invoice_items(): void
    {
        $tax = Tax::create([
            'name' => 'IVA',
            'code' => 'IVA',
            'rate' => 21.00,
            'region' => 'spain',
            'active' => true,
        ]);

        $user = User::factory()->create(['role' => 'viticulturist']);
        $invoice = \App\Models\Invoice::factory()->create(['user_id' => $user->id]);

        $item1 = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'tax_id' => $tax->id,
            'name' => 'Item 1',
            'quantity' => 1,
            'unit_price' => 100.00,
            'tax_rate' => 21.00,
        ]);

        $item2 = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'tax_id' => $tax->id,
            'name' => 'Item 2',
            'quantity' => 2,
            'unit_price' => 50.00,
            'tax_rate' => 21.00,
        ]);

        $this->assertCount(2, $tax->invoiceItems);
        $this->assertTrue($tax->invoiceItems->contains('id', $item1->id));
        $this->assertTrue($tax->invoiceItems->contains('id', $item2->id));
    }

    public function test_scope_active_filters_active_taxes(): void
    {
        $activeTax = Tax::create([
            'name' => 'IVA Activo',
            'code' => 'IVA',
            'rate' => 21.00,
            'region' => 'spain',
            'active' => true,
        ]);

        $inactiveTax = Tax::create([
            'name' => 'IVA Inactivo',
            'code' => 'IVA_OLD',
            'rate' => 18.00,
            'region' => 'spain',
            'active' => false,
        ]);

        $results = Tax::active()->get();

        $this->assertTrue($results->contains('id', $activeTax->id));
        $this->assertFalse($results->contains('id', $inactiveTax->id));
    }

    public function test_scope_default_filters_default_taxes(): void
    {
        $defaultTax = Tax::create([
            'name' => 'IVA Por Defecto',
            'code' => 'IVA',
            'rate' => 21.00,
            'region' => 'spain',
            'is_default' => true,
            'active' => true,
        ]);

        $nonDefaultTax = Tax::create([
            'name' => 'IVA No Por Defecto',
            'code' => 'IVA2',
            'rate' => 21.00,
            'region' => 'spain',
            'is_default' => false,
            'active' => true,
        ]);

        $results = Tax::default()->get();

        $this->assertTrue($results->contains('id', $defaultTax->id));
        $this->assertFalse($results->contains('id', $nonDefaultTax->id));
    }

    public function test_scope_for_region_filters_by_region(): void
    {
        $spainTax = Tax::create([
            'name' => 'IVA España',
            'code' => 'IVA',
            'rate' => 21.00,
            'region' => 'spain',
            'active' => true,
        ]);

        $canaryTax = Tax::create([
            'name' => 'IGIC Canarias',
            'code' => 'IGIC',
            'rate' => 7.00,
            'region' => 'canary',
            'active' => true,
        ]);

        $results = Tax::forRegion('spain')->get();

        $this->assertTrue($results->contains('id', $spainTax->id));
        $this->assertFalse($results->contains('id', $canaryTax->id));
    }

    public function test_formatted_rate_returns_percentage_string(): void
    {
        $tax = Tax::create([
            'name' => 'IVA',
            'code' => 'IVA',
            'rate' => 21.00,
            'region' => 'spain',
            'active' => true,
        ]);

        $this->assertEquals('21.00%', $tax->formatted_rate);
    }

    public function test_formatted_rate_handles_decimal_rates(): void
    {
        $tax = Tax::create([
            'name' => 'IGIC',
            'code' => 'IGIC',
            'rate' => 7.50,
            'region' => 'canary',
            'active' => true,
        ]);

        $this->assertEquals('7.50%', $tax->formatted_rate);
    }

    public function test_rate_is_cast_to_decimal(): void
    {
        $tax = Tax::create([
            'name' => 'IVA',
            'code' => 'IVA',
            'rate' => 21.00,
            'region' => 'spain',
            'active' => true,
        ]);

        // Laravel's decimal cast returns a string, not a float
        $this->assertIsNumeric($tax->rate);
        $this->assertEquals('21.00', $tax->rate);
    }

    public function test_is_default_and_active_are_cast_to_boolean(): void
    {
        $tax = Tax::create([
            'name' => 'IVA',
            'code' => 'IVA',
            'rate' => 21.00,
            'region' => 'spain',
            'is_default' => true,
            'active' => true,
        ]);

        $this->assertIsBool($tax->is_default);
        $this->assertIsBool($tax->active);
    }

    public function test_user_tax_pivot_includes_timestamps(): void
    {
        $tax = Tax::create([
            'name' => 'IVA',
            'code' => 'IVA',
            'rate' => 21.00,
            'region' => 'spain',
            'active' => true,
        ]);

        $user = User::factory()->create(['role' => 'viticulturist']);

        $tax->users()->attach($user->id, ['is_default' => true, 'order' => 1]);

        $pivot = $tax->users()->first()->pivot;
        $this->assertNotNull($pivot->created_at);
        $this->assertNotNull($pivot->updated_at);
    }

    public function test_user_tax_pivot_includes_custom_fields(): void
    {
        $tax = Tax::create([
            'name' => 'IVA',
            'code' => 'IVA',
            'rate' => 21.00,
            'region' => 'spain',
            'active' => true,
        ]);

        $user = User::factory()->create(['role' => 'viticulturist']);

        $tax->users()->attach($user->id, [
            'is_default' => true,
            'order' => 5,
        ]);

        // Refresh the relationship to load pivot data
        $tax->refresh();
        $pivot = $tax->users()->first()->pivot;
        $this->assertEquals(1, $pivot->is_default); // Boolean is stored as 1/0 in DB
        $this->assertEquals(5, $pivot->order);
    }
}

