<?php

namespace Tests\Unit\Models;

use App\Models\InvoicingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicingSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoicing_setting_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-',
            'invoice_padding' => 4,
            'invoice_counter' => 1,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
        ]);

        $this->assertEquals($user->id, $setting->user->id);
    }

    public function test_scope_for_user_filters_correctly(): void
    {
        $user1 = User::factory()->create(['role' => 'viticulturist']);
        $user2 = User::factory()->create(['role' => 'viticulturist']);

        $setting1 = InvoicingSetting::create([
            'user_id' => $user1->id,
            'invoice_prefix' => 'FAC-',
            'invoice_padding' => 4,
            'invoice_counter' => 1,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
        ]);

        $setting2 = InvoicingSetting::create([
            'user_id' => $user2->id,
            'invoice_prefix' => 'FAC-',
            'invoice_padding' => 4,
            'invoice_counter' => 1,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
        ]);

        $results = InvoicingSetting::forUser($user1->id)->get();
        
        $this->assertCount(1, $results);
        $this->assertEquals($setting1->id, $results->first()->id);
    }

    public function test_generate_invoice_code_returns_formatted_code(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-2025-',
            'invoice_padding' => 4,
            'invoice_counter' => 5,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
        ]);

        $code = $setting->generateInvoiceCode();

        $this->assertEquals('FAC-2025-0005', $code);
    }

    public function test_generate_invoice_code_increments_counter(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-',
            'invoice_padding' => 4,
            'invoice_counter' => 10,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
        ]);

        $initialCounter = $setting->invoice_counter;
        $setting->generateInvoiceCode();
        $setting->incrementInvoiceCounter();

        $this->assertEquals($initialCounter + 1, $setting->fresh()->invoice_counter);
    }

    public function test_generate_invoice_code_replaces_year_variable(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-{YEAR}-',
            'invoice_padding' => 4,
            'invoice_counter' => 1,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
        ]);

        $code = $setting->generateInvoiceCode();

        $this->assertStringContainsString(date('Y'), $code);
        $this->assertStringNotContainsString('{YEAR}', $code);
    }

    public function test_generate_invoice_code_replaces_month_and_day_variables(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => '{YEAR}-{MONTH}-{DAY}-',
            'invoice_padding' => 4,
            'invoice_counter' => 1,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
        ]);

        $code = $setting->generateInvoiceCode();

        $this->assertStringContainsString(date('Y'), $code);
        $this->assertStringContainsString(date('m'), $code);
        $this->assertStringContainsString(date('d'), $code);
        $this->assertStringNotContainsString('{MONTH}', $code);
        $this->assertStringNotContainsString('{DAY}', $code);
    }

    public function test_generate_delivery_note_code_returns_formatted_code(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-',
            'invoice_padding' => 4,
            'invoice_counter' => 1,
            'delivery_note_prefix' => 'ALB-2025-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 15,
        ]);

        $code = $setting->generateDeliveryNoteCode();

        $this->assertEquals('ALB-2025-0015', $code);
    }

    public function test_increment_invoice_counter_increments_value(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-',
            'invoice_padding' => 4,
            'invoice_counter' => 5,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
        ]);

        $setting->incrementInvoiceCounter();

        $this->assertEquals(6, $setting->fresh()->invoice_counter);
    }

    public function test_increment_delivery_note_counter_increments_value(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-',
            'invoice_padding' => 4,
            'invoice_counter' => 1,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 10,
        ]);

        $setting->incrementDeliveryNoteCounter();

        $this->assertEquals(11, $setting->fresh()->delivery_note_counter);
    }

    public function test_reset_invoice_counter_resets_to_one(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-',
            'invoice_padding' => 4,
            'invoice_counter' => 100,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
            'last_reset_year' => 2024,
        ]);

        $setting->resetInvoiceCounter();

        $this->assertEquals(1, $setting->fresh()->invoice_counter);
        $this->assertEquals(now()->year, $setting->fresh()->last_reset_year);
    }

    public function test_reset_delivery_note_counter_resets_to_one(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-',
            'invoice_padding' => 4,
            'invoice_counter' => 1,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 50,
            'last_reset_year' => 2024,
        ]);

        $setting->resetDeliveryNoteCounter();

        $this->assertEquals(1, $setting->fresh()->delivery_note_counter);
        $this->assertEquals(now()->year, $setting->fresh()->last_reset_year);
    }

    public function test_check_year_reset_resets_invoice_counter_when_enabled_and_year_changed(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-',
            'invoice_padding' => 4,
            'invoice_counter' => 100,
            'invoice_year_reset' => true,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
            'last_reset_year' => now()->year - 1, // Año anterior
        ]);

        $setting->generateInvoiceCode();

        $this->assertEquals(1, $setting->fresh()->invoice_counter);
        $this->assertEquals(now()->year, $setting->fresh()->last_reset_year);
    }

    public function test_check_year_reset_does_not_reset_when_disabled(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-',
            'invoice_padding' => 4,
            'invoice_counter' => 100,
            'invoice_year_reset' => false,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
            'last_reset_year' => now()->year - 1,
        ]);

        $initialCounter = $setting->invoice_counter;
        $setting->generateInvoiceCode();

        $this->assertEquals($initialCounter, $setting->fresh()->invoice_counter);
    }

    public function test_get_invoice_preview_returns_formatted_preview(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-{YEAR}-',
            'invoice_padding' => 4,
            'invoice_counter' => 42,
            'delivery_note_prefix' => 'ALB-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 1,
        ]);

        $preview = $setting->getInvoicePreview();

        $this->assertStringContainsString('FAC-', $preview);
        $this->assertStringContainsString('0042', $preview);
        $this->assertStringContainsString(date('Y'), $preview);
    }

    public function test_get_delivery_note_preview_returns_formatted_preview(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        
        $setting = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'FAC-',
            'invoice_padding' => 4,
            'invoice_counter' => 1,
            'delivery_note_prefix' => 'ALB-{YEAR}-',
            'delivery_note_padding' => 4,
            'delivery_note_counter' => 99,
        ]);

        $preview = $setting->getDeliveryNotePreview();

        $this->assertStringContainsString('ALB-', $preview);
        $this->assertStringContainsString('0099', $preview);
        $this->assertStringContainsString(date('Y'), $preview);
    }

    public function test_create_default_for_user_creates_with_default_values(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $setting = InvoicingSetting::createDefaultForUser($user->id);

        $this->assertEquals($user->id, $setting->user_id);
        $this->assertStringContainsString('FAC-', $setting->invoice_prefix);
        $this->assertStringContainsString('ALB-', $setting->delivery_note_prefix);
        $this->assertEquals(4, $setting->invoice_padding);
        $this->assertEquals(4, $setting->delivery_note_padding);
        $this->assertEquals(1, $setting->invoice_counter);
        $this->assertEquals(1, $setting->delivery_note_counter);
        $this->assertTrue($setting->invoice_year_reset);
        $this->assertTrue($setting->delivery_note_year_reset);
        $this->assertEquals(now()->year, $setting->last_reset_year);
    }

    public function test_get_or_create_for_user_returns_existing_setting(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $existing = InvoicingSetting::create([
            'user_id' => $user->id,
            'invoice_prefix' => 'CUSTOM-',
            'invoice_padding' => 6,
            'invoice_counter' => 50,
            'delivery_note_prefix' => 'CUSTOM-ALB-',
            'delivery_note_padding' => 6,
            'delivery_note_counter' => 50,
        ]);

        $setting = InvoicingSetting::getOrCreateForUser($user->id);

        $this->assertEquals($existing->id, $setting->id);
        $this->assertEquals('CUSTOM-', $setting->invoice_prefix);
    }

    public function test_get_or_create_for_user_creates_new_when_not_exists(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $setting = InvoicingSetting::getOrCreateForUser($user->id);

        $this->assertNotNull($setting);
        $this->assertEquals($user->id, $setting->user_id);
        $this->assertStringContainsString('FAC-', $setting->invoice_prefix);
    }
}

