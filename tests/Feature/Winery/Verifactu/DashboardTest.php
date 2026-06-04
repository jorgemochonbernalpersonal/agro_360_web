<?php

namespace Tests\Feature\Winery\Verifactu;

use App\Livewire\Winery\Verifactu\Dashboard;
use App\Models\Invoice;
use App\Services\VerifactuService;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class DashboardTest extends WineryTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_winery_can_access_verifactu_dashboard(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('winery.verifactu.index'))
            ->assertOk();
    }

    public function test_component_renders(): void
    {
        $winery = $this->makeWinery();

        Livewire::actingAs($winery)
            ->test(Dashboard::class)
            ->assertOk();
    }

    // ── tab navigation ────────────────────────────────────────────────────────

    public function test_default_tab_is_pending(): void
    {
        $winery = $this->makeWinery();

        Livewire::actingAs($winery)
            ->test(Dashboard::class)
            ->assertSet('tab', 'pending');
    }

    public function test_can_switch_tabs(): void
    {
        $winery = $this->makeWinery();

        Livewire::actingAs($winery)
            ->test(Dashboard::class)
            ->set('tab', 'sent')
            ->assertSet('tab', 'sent')
            ->set('tab', 'errors')
            ->assertSet('tab', 'errors')
            ->set('tab', 'config')
            ->assertSet('tab', 'config');
    }

    // ── exclude ───────────────────────────────────────────────────────────────

    public function test_winery_can_exclude_own_invoice(): void
    {
        $winery = $this->makeWinery();
        $invoice = $this->makeInvoice($winery->id, ['sif_status' => 'pendiente', 'sif_excluded' => false]);

        Livewire::actingAs($winery)
            ->test(Dashboard::class)
            ->call('excludeInvoice', $invoice->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'sif_excluded' => true,
        ]);
    }

    public function test_cannot_exclude_other_winery_invoice(): void
    {
        $winery1 = $this->makeWinery();
        $winery2 = $this->makeOtherWinery();
        $invoice = $this->makeInvoice($winery2->id, ['sif_status' => 'pendiente']);

        Livewire::actingAs($winery1)
            ->test(Dashboard::class)
            ->call('excludeInvoice', $invoice->id);

        // Invoice must remain un-excluded
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'sif_excluded' => false,
        ]);
    }

    // ── sendInvoice delegates to service ─────────────────────────────────────

    public function test_send_invoice_calls_verifactu_service(): void
    {
        $winery = $this->makeWinery();
        $invoice = $this->makeInvoice($winery->id, ['sif_status' => 'pendiente']);

        $this->mock(VerifactuService::class, function ($mock) use ($invoice) {
            $mock->shouldReceive('send')
                ->once()
                ->with(\Mockery::on(fn ($i) => $i->id === $invoice->id))
                ->andReturn(['success' => true, 'csv' => 'ABC123', 'errors' => []]);
        });

        Livewire::actingAs($winery)
            ->test(Dashboard::class)
            ->call('sendInvoice', $invoice->id)
            ->assertDispatched('toast');
    }

    public function test_send_invoice_shows_error_on_failure(): void
    {
        $winery = $this->makeWinery();
        $invoice = $this->makeInvoice($winery->id, ['sif_status' => 'pendiente']);

        $this->mock(VerifactuService::class, function ($mock) {
            $mock->shouldReceive('send')
                ->once()
                ->andReturn(['success' => false, 'csv' => null, 'errors' => ['Error de firma']]);
        });

        Livewire::actingAs($winery)
            ->test(Dashboard::class)
            ->call('sendInvoice', $invoice->id)
            ->assertDispatched('toast');
    }
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeInvoice(int $wineryId, array $attrs = []): Invoice
    {
        return Invoice::create(array_merge([
            'user_id' => $wineryId,
            'invoice_number' => 'F-'.rand(1000, 9999),
            'invoice_date' => now()->format('Y-m-d'),
            'total_amount' => 100.00,
            'status' => 'sent',
            'sif_status' => 'pendiente',
        ], $attrs));
    }
}
