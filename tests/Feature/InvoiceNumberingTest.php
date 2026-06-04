<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoicingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests para la numeración secuencial de facturas y albaranes.
 *
 * Cubre:
 * - Generación secuencial y atómica de InvoicingSetting
 * - Previews sin side-effects (no modifican el contador en BD)
 * - Contadores independientes (factura vs albarán)
 * - Reseteo anual
 * - invoice_number asignado desde el estado draft
 * - Guard: draft con número SÍ puede cancelarse
 * - Guard: sent NO puede cancelarse (requiere rectificativa)
 * - Fallback: facturas antiguas sin invoice_number lo reciben al pasar a sent
 */
class InvoiceNumberingTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Traits\CreatesTestHarvest;

    protected User $user;

    protected Client $client;

    protected InvoicingSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);

        $this->settings = InvoicingSetting::createDefaultForUser($this->user->id);

        $this->actingAs($this->user);
    }

    // -------------------------------------------------------------------------
    // InvoicingSetting — generación secuencial
    // -------------------------------------------------------------------------

    public function test_invoice_code_increments_sequentially()
    {
        $code1 = $this->settings->generateAndIncrementInvoiceCode();
        $code2 = $this->settings->generateAndIncrementInvoiceCode();
        $code3 = $this->settings->generateAndIncrementInvoiceCode();

        $this->assertStringEndsWith('0001', $code1);
        $this->assertStringEndsWith('0002', $code2);
        $this->assertStringEndsWith('0003', $code3);
    }

    public function test_delivery_note_code_increments_sequentially()
    {
        $code1 = $this->settings->generateAndIncrementDeliveryNoteCode();
        $code2 = $this->settings->generateAndIncrementDeliveryNoteCode();
        $code3 = $this->settings->generateAndIncrementDeliveryNoteCode();

        $this->assertStringEndsWith('0001', $code1);
        $this->assertStringEndsWith('0002', $code2);
        $this->assertStringEndsWith('0003', $code3);
    }

    public function test_invoice_and_delivery_note_counters_are_independent()
    {
        // Avanzar el contador de albaranes varias veces
        $this->settings->generateAndIncrementDeliveryNoteCode();
        $this->settings->generateAndIncrementDeliveryNoteCode();
        $this->settings->generateAndIncrementDeliveryNoteCode();

        // El contador de facturas debe seguir en 1
        $invoiceCode = $this->settings->generateAndIncrementInvoiceCode();
        $this->assertStringEndsWith('0001', $invoiceCode);

        // Y viceversa: avanzar facturas no afecta albaranes
        $this->settings->generateAndIncrementInvoiceCode();
        $deliveryCode = $this->settings->generateAndIncrementDeliveryNoteCode();
        $this->assertStringEndsWith('0004', $deliveryCode);
    }

    public function test_invoice_code_includes_year_in_prefix_by_default()
    {
        $code = $this->settings->generateAndIncrementInvoiceCode();
        $currentYear = now()->format('Y');

        $this->assertStringContainsString($currentYear, $code);
    }

    public function test_delivery_note_code_includes_year_in_prefix_by_default()
    {
        $code = $this->settings->generateAndIncrementDeliveryNoteCode();
        $currentYear = now()->format('Y');

        $this->assertStringContainsString($currentYear, $code);
    }

    // -------------------------------------------------------------------------
    // Previews — sin side-effects
    // -------------------------------------------------------------------------

    public function test_invoice_preview_does_not_increment_counter()
    {
        $preview1 = $this->settings->getInvoicePreview();
        $preview2 = $this->settings->getInvoicePreview();
        $preview3 = $this->settings->getInvoicePreview();

        // Todos los previews devuelven el mismo valor
        $this->assertEquals($preview1, $preview2);
        $this->assertEquals($preview2, $preview3);

        // El contador en BD no ha cambiado (sigue en 1)
        $this->settings->refresh();
        $this->assertEquals(1, $this->settings->invoice_counter);
    }

    public function test_delivery_note_preview_does_not_increment_counter()
    {
        $preview1 = $this->settings->getDeliveryNotePreview();
        $preview2 = $this->settings->getDeliveryNotePreview();

        $this->assertEquals($preview1, $preview2);

        $this->settings->refresh();
        $this->assertEquals(1, $this->settings->delivery_note_counter);
    }

    public function test_preview_matches_next_generated_code()
    {
        $invoicePreview = $this->settings->getInvoicePreview();
        $deliveryNotePreview = $this->settings->getDeliveryNotePreview();

        $invoiceCode = $this->settings->generateAndIncrementInvoiceCode();
        $deliveryNoteCode = $this->settings->generateAndIncrementDeliveryNoteCode();

        $this->assertEquals($invoicePreview, $invoiceCode);
        $this->assertEquals($deliveryNotePreview, $deliveryNoteCode);
    }

    // -------------------------------------------------------------------------
    // Reseteo anual
    // -------------------------------------------------------------------------

    public function test_invoice_counter_resets_on_new_year_when_year_reset_enabled()
    {
        // Simular que los settings fueron creados en el año pasado
        $lastYear = now()->year - 1;
        $this->settings->update([
            'invoice_year_reset' => true,
            'invoice_counter' => 42,
            'invoice_last_reset_year' => $lastYear,
        ]);

        // Al generar el primer código del año actual debe resetear a 1
        $code = $this->settings->generateAndIncrementInvoiceCode();
        $this->assertStringEndsWith('0001', $code);

        // Y el last_reset_year debe actualizarse al año actual
        $this->settings->refresh();
        $this->assertEquals(now()->year, $this->settings->invoice_last_reset_year);
    }

    public function test_delivery_note_counter_resets_on_new_year_when_enabled()
    {
        $lastYear = now()->year - 1;
        $this->settings->update([
            'delivery_note_year_reset' => true,
            'delivery_note_counter' => 21,
            'delivery_note_last_reset_year' => $lastYear,
        ]);

        $code = $this->settings->generateAndIncrementDeliveryNoteCode();
        $this->assertStringEndsWith('0001', $code);

        $this->settings->refresh();
        $this->assertEquals(now()->year, $this->settings->delivery_note_last_reset_year);
    }

    public function test_invoice_counter_does_not_reset_when_year_reset_disabled()
    {
        $lastYear = now()->year - 1;
        $this->settings->update([
            'invoice_year_reset' => false,
            'invoice_counter' => 42,
            'invoice_last_reset_year' => $lastYear,
        ]);

        $code = $this->settings->generateAndIncrementInvoiceCode();
        $this->assertStringEndsWith('0042', $code);
    }

    public function test_year_reset_of_one_counter_does_not_affect_the_other()
    {
        $lastYear = now()->year - 1;
        $this->settings->update([
            // Solo el contador de albaranes tiene reset pendiente
            'invoice_year_reset' => true,
            'invoice_counter' => 10,
            'invoice_last_reset_year' => now()->year, // ya reseteado este año

            'delivery_note_year_reset' => true,
            'delivery_note_counter' => 21,
            'delivery_note_last_reset_year' => $lastYear,  // pendiente de resetear
        ]);

        // El albarán se resetea porque su last_reset_year es del año pasado
        $albaran = $this->settings->generateAndIncrementDeliveryNoteCode();
        $this->assertStringEndsWith('0001', $albaran);

        // La factura NO se resetea porque su last_reset_year ya es este año
        $factura = $this->settings->generateAndIncrementInvoiceCode();
        $this->assertStringEndsWith('0010', $factura);
    }

    // -------------------------------------------------------------------------
    // Configuración manual del contador (Ajustes → Numeración)
    // -------------------------------------------------------------------------

    public function test_counter_can_be_set_manually_to_any_value()
    {
        $this->settings->update(['invoice_counter' => 50]);

        $code = $this->settings->generateAndIncrementInvoiceCode();
        $this->assertStringEndsWith('0050', $code);

        $nextCode = $this->settings->generateAndIncrementInvoiceCode();
        $this->assertStringEndsWith('0051', $nextCode);
    }

    // -------------------------------------------------------------------------
    // invoice_number asignado en draft (nuevo comportamiento)
    // -------------------------------------------------------------------------

    public function test_draft_invoice_created_via_factory_has_invoice_number()
    {
        // El factory por defecto incluye invoice_number
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $this->assertNotNull($invoice->invoice_number);
        $this->assertNotEmpty($invoice->invoice_number);
    }

    public function test_two_invoices_get_sequential_numbers_when_settings_exist()
    {
        $invoice1 = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => $this->settings->generateAndIncrementInvoiceCode(),
        ]);

        $invoice2 = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => $this->settings->generateAndIncrementInvoiceCode(),
        ]);

        // Los números no deben ser iguales
        $this->assertNotEquals($invoice1->invoice_number, $invoice2->invoice_number);

        // invoice2 debe ser el siguiente al invoice1
        $num1 = (int) substr($invoice1->invoice_number, -4);
        $num2 = (int) substr($invoice2->invoice_number, -4);
        $this->assertEquals($num1 + 1, $num2);
    }

    // -------------------------------------------------------------------------
    // Guards en InvoiceObserver
    // -------------------------------------------------------------------------

    public function test_draft_invoice_with_number_can_be_cancelled()
    {
        // Los borradores con número asignado SÍ pueden cancelarse
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'FAC-2026-0001',
        ]);

        // No debe lanzar excepción
        $invoice->update(['status' => 'cancelled']);

        $this->assertEquals('cancelled', $invoice->fresh()->status);
    }

    public function test_draft_invoice_without_number_can_be_cancelled()
    {
        // Un borrador sin número también puede cancelarse
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => null,
        ]);

        $invoice->update(['status' => 'cancelled']);

        $this->assertEquals('cancelled', $invoice->fresh()->status);
    }

    public function test_sent_invoice_cannot_be_cancelled()
    {
        // Una factura ya enviada (formalmente emitida) NO puede cancelarse
        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'FAC-2026-0001',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/rectificativa/i');

        $invoice->update(['status' => 'cancelled']);
    }

    public function test_cancelling_already_cancelled_invoice_does_not_throw()
    {
        // Cancelar una factura ya cancelada no debe lanzar excepción
        $invoice = Invoice::factory()->cancelled()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'FAC-2026-0005',
        ]);

        // No debe lanzar excepción (oldStatus == 'cancelled')
        $invoice->update(['status' => 'cancelled', 'observations' => 'Nota']);

        $this->assertEquals('cancelled', $invoice->fresh()->status);
    }

    public function test_delivered_delivery_status_cannot_be_changed_to_cancelled()
    {
        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'delivery_status' => 'delivered',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/entrega/i');

        $invoice->update(['delivery_status' => 'cancelled']);
    }

    // -------------------------------------------------------------------------
    // Fallback: facturas antiguas sin invoice_number
    // -------------------------------------------------------------------------

    public function test_old_draft_without_invoice_number_receives_one_when_sent()
    {
        // Simular factura antigua (creada antes del nuevo sistema) sin invoice_number
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => null,
        ]);

        $this->assertNull($invoice->invoice_number);

        // El observer debe generar el número al pasar a sent
        $invoice->update(['status' => 'sent']);

        $invoice->refresh();
        $this->assertNotNull($invoice->invoice_number);
        $this->assertNotEmpty($invoice->invoice_number);
    }

    public function test_sent_invoice_with_existing_number_does_not_change_number()
    {
        // Si ya tiene número, el observer no debe re-generarlo
        $originalNumber = 'FAC-2026-0001';

        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'invoice_number' => $originalNumber,
        ]);

        $invoice->update(['status' => 'sent']);

        $invoice->refresh();
        $this->assertEquals($originalNumber, $invoice->invoice_number);
    }

    // -------------------------------------------------------------------------
    // getOrCreateForUser
    // -------------------------------------------------------------------------

    public function test_get_or_create_returns_existing_settings()
    {
        // Avanzar el contador
        $this->settings->update(['invoice_counter' => 15]);

        // getOrCreateForUser debe devolver los mismos settings (no crear nuevos)
        $retrieved = InvoicingSetting::getOrCreateForUser($this->user->id);

        $this->assertEquals($this->settings->id, $retrieved->id);
        $this->assertEquals(15, $retrieved->invoice_counter);
    }

    public function test_get_or_create_creates_settings_for_new_user()
    {
        $newUser = User::factory()->create();

        $this->assertNull(InvoicingSetting::forUser($newUser->id)->first());

        $settings = InvoicingSetting::getOrCreateForUser($newUser->id);

        $this->assertNotNull($settings);
        $this->assertEquals($newUser->id, $settings->user_id);
        $this->assertEquals(1, $settings->invoice_counter);
        $this->assertEquals(1, $settings->delivery_note_counter);
    }
}
