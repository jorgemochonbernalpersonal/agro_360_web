<?php

namespace Tests\Feature\Winery;

use App\Livewire\Winery\Settings;
use App\Models\InvoicingSetting;
use App\Models\Tax;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

/**
 * Tests for the Winery Settings component.
 *
 * Covers: rendering, invoicing settings, plot settings,
 *         fiscal data, and tax selection.
 */
class SettingsTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    // ── Rendering ─────────────────────────────────────────────────────────────

    public function test_settings_page_renders(): void
    {
        $this->get(route('winery.settings'))
            ->assertOk();
    }

    // ── Invoicing tab ─────────────────────────────────────────────────────────

    public function test_can_save_invoicing_settings(): void
    {
        // Ensure InvoicingSetting exists
        InvoicingSetting::createDefaultForUser($this->winery->id);

        Livewire::test(Settings::class)
            ->set('invoice_prefix', 'FAC-{YEAR}-')
            ->set('invoice_padding', 4)
            ->set('invoice_counter', 1)
            ->set('delivery_note_prefix', 'ALB-{YEAR}-')
            ->set('delivery_note_padding', 4)
            ->set('delivery_note_counter', 1)
            ->call('saveInvoicing')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoicing_settings', [
            'user_id'        => $this->winery->id,
            'invoice_prefix' => 'FAC-{YEAR}-',
        ]);
    }

    public function test_saveInvoicing_validates_required_fields(): void
    {
        InvoicingSetting::createDefaultForUser($this->winery->id);

        Livewire::test(Settings::class)
            ->set('invoice_prefix', '')
            ->set('delivery_note_prefix', '')
            ->call('saveInvoicing')
            ->assertHasErrors(['invoice_prefix', 'delivery_note_prefix']);
    }

    public function test_saveInvoicing_validates_padding_range(): void
    {
        InvoicingSetting::createDefaultForUser($this->winery->id);

        Livewire::test(Settings::class)
            ->set('invoice_prefix', 'FAC-')
            ->set('invoice_padding', 1)  // below min:2
            ->set('invoice_counter', 1)
            ->set('delivery_note_prefix', 'ALB-')
            ->set('delivery_note_padding', 4)
            ->set('delivery_note_counter', 1)
            ->call('saveInvoicing')
            ->assertHasErrors(['invoice_padding']);
    }

    // ── Plots tab ─────────────────────────────────────────────────────────────

    public function test_can_save_plot_settings(): void
    {
        Livewire::test(Settings::class)
            ->set('default_limit_kg_per_ha', '8000')
            ->call('savePlots')
            ->assertHasNoErrors();
    }

    public function test_savePlots_rejects_negative_value(): void
    {
        Livewire::test(Settings::class)
            ->set('default_limit_kg_per_ha', '-100')
            ->call('savePlots')
            ->assertHasErrors(['default_limit_kg_per_ha']);
    }

    // ── Fiscal tab ────────────────────────────────────────────────────────────

    public function test_can_save_fiscal_data(): void
    {
        InvoicingSetting::createDefaultForUser($this->winery->id);

        Livewire::test(Settings::class)
            ->set('fiscal_nif', '12345678A')
            ->set('fiscal_legal_name', 'Bodega Test S.L.')
            ->set('fiscal_address', 'Calle Mayor, 1')
            ->set('fiscal_city', 'Madrid')
            ->set('fiscal_postal_code', '28001')
            ->set('fiscal_phone', '611223344')
            ->call('saveFiscal')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id'  => $this->winery->id,
            'dni' => '12345678A',
        ]);
    }

    // ── Tax selection ─────────────────────────────────────────────────────────

    public function test_can_select_tax(): void
    {
        $tax = Tax::firstOrCreate(['code' => 'IVA21'], ['name' => 'IVA 21%', 'rate' => 21.0]);

        Livewire::test(Settings::class)
            ->call('selectTax', $tax->id);

        $this->assertDatabaseHas('user_taxes', [
            'user_id' => $this->winery->id,
            'tax_id'  => $tax->id,
        ]);
    }

    // ── Reset counters ────────────────────────────────────────────────────────

    public function test_can_reset_invoice_counter(): void
    {
        InvoicingSetting::createDefaultForUser($this->winery->id);

        Livewire::test(Settings::class)
            ->call('resetInvoiceCounter')
            ->assertSet('invoice_counter', 1);
    }

    public function test_can_reset_delivery_note_counter(): void
    {
        InvoicingSetting::createDefaultForUser($this->winery->id);

        Livewire::test(Settings::class)
            ->call('resetDeliveryNoteCounter')
            ->assertSet('delivery_note_counter', 1);
    }
}
