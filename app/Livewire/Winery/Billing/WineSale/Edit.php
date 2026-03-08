<?php

namespace App\Livewire\Winery\Billing\WineSale;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\UserTax;
use App\Models\WineLot;
use App\Services\WineStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public Invoice $invoice;

    public string $client_id      = '';
    public string $invoice_date   = '';
    public string $observations   = '';
    public string $payment_type   = '';
    public string $payment_status = '';

    public array $lines = [];

    protected float $defaultTaxRate = 0.0;

    public function mount(int $id): void
    {
        $this->invoice = Invoice::where('user_id', Auth::id())
            ->where('invoice_type', 'wine_sale')
            ->with('items.wineLot')
            ->findOrFail($id);

        $this->client_id      = (string) $this->invoice->client_id;
        $this->invoice_date   = $this->invoice->invoice_date
            ? $this->invoice->invoice_date->format('Y-m-d')
            : '';
        $this->observations   = $this->invoice->observations ?? '';
        $this->payment_type   = $this->invoice->payment_type ?? '';
        $this->payment_status = $this->invoice->payment_status ?? 'unpaid';

        $userTax = UserTax::where('user_id', Auth::id())->with('tax')->first();
        $this->defaultTaxRate = $userTax?->tax?->rate ?? 0.0;

        $this->lines = $this->invoice->items->map(fn ($item) => [
            'wine_lot_id' => (string) $item->wine_lot_id,
            'quantity'    => (string) $item->quantity,
            'unit_price'  => (string) $item->unit_price,
            'tax_rate'    => (string) $item->tax_rate,
            'description' => $item->description ?? '',
        ])->toArray();

        if (empty($this->lines)) {
            $this->addLine();
        }
    }

    public function getIsLockedProperty(): bool
    {
        return $this->invoice->delivery_status === 'delivered'
            || $this->invoice->status === 'cancelled';
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'wine_lot_id' => '',
            'quantity'    => '',
            'unit_price'  => '',
            'tax_rate'    => (string) $this->defaultTaxRate,
            'description' => '',
        ];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) <= 1) return;
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
    }

    public function updatedLinesWineLotId(string $value, string $index): void
    {
        if ($value) {
            $lot = WineLot::where('user_id', Auth::id())->find($value);
            if ($lot && $lot->price_per_unit) {
                $this->lines[(int) $index]['unit_price'] = (string) $lot->price_per_unit;
            }
        }
    }

    protected function rules(): array
    {
        if ($this->isLocked) {
            return [
                'payment_status' => 'required|in:unpaid,partial,paid',
                'payment_type'   => 'nullable|in:cash,transfer,check,other',
            ];
        }

        return [
            'client_id'           => 'required|exists:clients,id',
            'invoice_date'        => 'required|date',
            'payment_type'        => 'nullable|in:cash,transfer,check,other',
            'payment_status'      => 'required|in:unpaid,partial,paid',
            'observations'        => 'nullable|string',
            'lines'               => 'required|array|min:1',
            'lines.*.wine_lot_id' => 'required|exists:wine_lots,id',
            'lines.*.quantity'    => 'required|numeric|min:0.001',
            'lines.*.unit_price'  => 'required|numeric|min:0',
            'lines.*.tax_rate'    => 'required|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string|max:255',
        ];
    }

    protected function validationAttributes(): array
    {
        $attrs = [
            'client_id'    => 'cliente',
            'invoice_date' => 'fecha',
        ];
        foreach ($this->lines as $i => $_) {
            $attrs["lines.{$i}.wine_lot_id"] = 'lote de vino';
            $attrs["lines.{$i}.quantity"]    = 'cantidad';
            $attrs["lines.{$i}.unit_price"]  = 'precio unitario';
            $attrs["lines.{$i}.tax_rate"]    = 'IVA';
        }
        return $attrs;
    }

    public function save()
    {
        $this->validate();

        // Locked invoices: only payment status/type can be updated
        if ($this->isLocked) {
            $this->invoice->update([
                'payment_status' => $this->payment_status,
                'payment_type'   => $this->payment_type ?: null,
            ]);
            $this->toastSuccess('Estado de cobro actualizado.');
            return $this->redirect(route('winery.invoices.wine-sale.index'), navigate: true);
        }

        $client = Client::where('user_id', Auth::id())->findOrFail($this->client_id);

        try {
            DB::transaction(function () use ($client) {
                // ── 1. Restore stock: reserved → available for all existing wine items
                WineStockService::moveForInvoice($this->invoice, 'cancel');

                // ── 2. Delete old items (withoutObservers: bulk delete doesn't fire
                //       observer events anyway, but explicit for clarity and safety)
                InvoiceItem::withoutObservers(fn () => $this->invoice->items()->delete());

                // ── 3. Calculate new totals
                $subtotal  = 0;
                $taxAmount = 0;

                foreach ($this->lines as $line) {
                    $lineSubtotal  = (float) $line['quantity'] * (float) $line['unit_price'];
                    $subtotal     += $lineSubtotal;
                    $taxAmount    += $lineSubtotal * ((float) $line['tax_rate'] / 100);
                }

                $total = $subtotal + $taxAmount;

                // ── 4. Update invoice header
                $this->invoice->update([
                    'client_id'            => $client->id,
                    'invoice_date'         => $this->invoice_date,
                    'billing_first_name'   => $client->first_name,
                    'billing_last_name'    => $client->last_name,
                    'billing_company_name' => $client->company_name,
                    'billing_email'        => $client->email,
                    'billing_phone'        => $client->phone,
                    'subtotal'             => round($subtotal, 3),
                    'tax_base'             => round($subtotal, 3),
                    'tax_amount'           => round($taxAmount, 3),
                    'total_amount'         => round($total, 3),
                    'payment_status'       => $this->payment_status,
                    'payment_type'         => $this->payment_type ?: null,
                    'observations'         => $this->observations ?: null,
                ]);

                // ── 5. Create new items + move stock (available → reserved)
                //       withoutObservers prevents InvoiceItemObserver from firing
                //       ContainerStockService; WineStockService is sole stock manager here.
                InvoiceItem::withoutObservers(function () {
                    foreach ($this->lines as $line) {
                        $lot = WineLot::where('user_id', Auth::id())
                            ->lockForUpdate()
                            ->findOrFail($line['wine_lot_id']);

                        $qty           = (float) $line['quantity'];
                        $unitPrice     = (float) $line['unit_price'];
                        $taxRate       = (float) $line['tax_rate'];
                        $subtotalLine  = round($qty * $unitPrice, 3);
                        $taxAmountLine = round($subtotalLine * ($taxRate / 100), 3);

                        $item = $this->invoice->items()->create([
                            'wine_lot_id'  => $lot->id,
                            'concept_type' => 'wine',
                            'name'         => $line['description']
                                ?: $lot->name . ($lot->vintage ? " ({$lot->vintage})" : ''),
                            'description'  => $line['description'] ?: null,
                            'quantity'     => $qty,
                            'unit_price'   => $unitPrice,
                            'tax_rate'     => $taxRate,
                            'subtotal'     => $subtotalLine,
                            'tax_base'     => $subtotalLine,
                            'tax_amount'   => $taxAmountLine,
                            'total'        => $subtotalLine + $taxAmountLine,
                        ]);

                        // throws RuntimeException if insufficient available stock
                        WineStockService::moveOnCreate($this->invoice, $item, $lot, $qty);
                    }
                });
            });

            $this->toastSuccess('Factura actualizada correctamente.');
            return $this->redirect(route('winery.invoices.wine-sale.index'), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al editar factura de vino: ' . $e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id'    => Auth::id(),
                'exception'  => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al guardar los cambios.');
        }
    }

    public function render()
    {
        $clients  = Client::where('user_id', Auth::id())->where('active', true)->orderBy('first_name')->orderBy('company_name')->get();
        $wineLots = WineLot::where('user_id', Auth::id())->where('archived', false)->orderByDesc('vintage')->orderBy('name')->get();

        return view('livewire.winery.billing.wine-sale.edit', [
            'clients'  => $clients,
            'wineLots' => $wineLots,
        ])->layout('layouts.app');
    }
}
