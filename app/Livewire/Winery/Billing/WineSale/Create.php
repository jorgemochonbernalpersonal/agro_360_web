<?php

namespace App\Livewire\Winery\Billing\WineSale;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\WineLot;
use App\Services\WineStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    // Header
    public string $client_id    = '';
    public string $invoice_date = '';
    public string $observations = '';
    public string $payment_type = '';

    // Lines (array of rows)
    public array $lines = [];

    public function mount(): void
    {
        $this->invoice_date = now()->toDateString();
        $this->addLine();
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'wine_lot_id' => '',
            'quantity'    => '',
            'unit_price'  => '',
            'tax_rate'    => '0',
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
            if ($lot) {
                $this->lines[(int) $index]['unit_price'] = (string) $lot->price_per_unit;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'client_id'           => 'required|exists:clients,id',
            'invoice_date'        => 'required|date',
            'payment_type'        => 'nullable|in:cash,transfer,check,other',
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

        // Verify client belongs to this winery
        $client = Client::where('user_id', Auth::id())->findOrFail($this->client_id);

        try {
            DB::beginTransaction();

            // ── Atomic sequential numbering (lockForUpdate prevents race conditions) ──
            DB::table('users')->where('id', Auth::id())->lockForUpdate()->first();
            DB::table('users')->where('id', Auth::id())->increment('wine_sale_seq');
            $seq  = DB::table('users')->where('id', Auth::id())->value('wine_sale_seq');
            $year = now()->year;

            $number   = 'WS-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            $noteCode = 'ALB-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // ── Calculate totals ──────────────────────────────────────────────────────
            $subtotal  = 0;
            $taxAmount = 0;

            foreach ($this->lines as $line) {
                $lineSubtotal  = (float) $line['quantity'] * (float) $line['unit_price'];
                $lineTax       = $lineSubtotal * ((float) $line['tax_rate'] / 100);
                $subtotal     += $lineSubtotal;
                $taxAmount    += $lineTax;
            }

            $total = $subtotal + $taxAmount;

            // ── Create invoice ────────────────────────────────────────────────────────
            $invoice = Invoice::create([
                'user_id'              => Auth::id(),
                'client_id'            => $client->id,
                'invoice_type'         => 'wine_sale',
                'invoice_number'       => $number,
                'delivery_note_code'   => $noteCode,
                'invoice_date'         => $this->invoice_date,
                'delivery_status'      => 'pending',
                'billing_first_name'   => $client->first_name,
                'billing_last_name'    => $client->last_name,
                'billing_company_name' => $client->company_name,
                'billing_email'        => $client->email,
                'billing_phone'        => $client->phone,
                'subtotal'             => round($subtotal, 3),
                'tax_base'             => round($subtotal, 3),
                'tax_amount'           => round($taxAmount, 3),
                'total_amount'         => round($total, 3),
                'status'               => 'draft',
                'payment_status'       => 'unpaid',
                'payment_type'         => $this->payment_type ?: null,
                'observations'         => $this->observations ?: null,
            ]);

            // ── Create items + move stock (available → reserved) ──────────────────────
            foreach ($this->lines as $line) {
                $lot = WineLot::where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->findOrFail($line['wine_lot_id']);

                $qty           = (float) $line['quantity'];
                $unitPrice     = (float) $line['unit_price'];
                $taxRate       = (float) $line['tax_rate'];
                $subtotalLine  = round($qty * $unitPrice, 3);
                $taxAmountLine = round($subtotalLine * ($taxRate / 100), 3);

                $item = InvoiceItem::create([
                    'invoice_id'   => $invoice->id,
                    'wine_lot_id'  => $lot->id,
                    'concept_type' => 'wine',
                    'name'         => $line['description'] ?: "{$lot->name}" . ($lot->vintage ? " ({$lot->vintage})" : ''),
                    'description'  => $line['description'] ?: null,
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'tax_rate'     => $taxRate,
                    'subtotal'     => $subtotalLine,
                    'tax_base'     => $subtotalLine,
                    'tax_amount'   => $taxAmountLine,
                    'total'        => $subtotalLine + $taxAmountLine,
                ]);

                // Move stock: available → reserved (throws RuntimeException if insufficient)
                WineStockService::moveOnCreate($invoice, $item, $lot, $qty);
            }

            DB::commit();

            $this->toastSuccess("Factura {$number} creada — Albarán: {$noteCode}");
            return $this->redirect(route('winery.invoices.wine-sale.index'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear factura de vino: ' . $e->getMessage(), [
                'user_id'   => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al crear la factura. Inténtalo de nuevo.');
        }
    }

    public function render()
    {
        $clients  = Client::where('user_id', Auth::id())->where('active', true)->orderBy('first_name')->orderBy('company_name')->get();
        $wineLots = WineLot::where('user_id', Auth::id())->where('archived', false)->orderByDesc('vintage')->orderBy('name')->get();

        return view('livewire.winery.billing.wine-sale.create', [
            'clients'  => $clients,
            'wineLots' => $wineLots,
        ])->layout('layouts.app');
    }
}
