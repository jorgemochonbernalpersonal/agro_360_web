<?php

namespace App\Livewire\Concerns;

use App\Models\Client;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\ProductLot;
use App\Models\Tax;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;

trait WithProducerInvoiceItems
{
    public array $items = [];

    public string $selectedHarvestId = '';

    public string $selectedCampaign = '';

    public string $selectedLotId = '';

    public $availableClients = [];

    public $availableAddresses = [];

    public $availableTaxes = [];

    public $availableHarvests = [];

    public $availableLots = [];

    protected InvoiceService $invoiceService;

    protected string $defaultTaxId = '';

    public function boot(InvoiceService $invoiceService): void
    {
        $this->invoiceService = $invoiceService;
    }

    public function loadTaxes(): void
    {
        $user = Auth::user();

        $this->availableTaxes = $user->taxes()->orderByPivot('order')->get();
        if ($this->availableTaxes->isEmpty()) {
            $this->availableTaxes = Tax::active()->orderBy('rate')->get();
        }

        $defaultTax = $user->defaultTax()->first() ?? $this->availableTaxes->first();
        $this->defaultTaxId = (string) ($defaultTax->id ?? '');
    }

    public function loadHarvests(): void
    {
        $user = Auth::user();

        $harvests = Harvest::whereHas('activity', fn ($q) => $q->where('viticulturist_id', $user->id))
            ->with(['activity.plot', 'plotPlanting.grapeVariety', 'activity.campaign', 'container'])
            ->when($this->selectedCampaign, fn ($q) => $q->whereHas('activity', fn ($q) => $q->where('campaign_id', $this->selectedCampaign)))
            ->where('total_weight', '>', 0)
            ->orderBy('harvest_start_date', 'desc')
            ->get();

        $latestStocks = HarvestStock::whereIn('harvest_id', $harvests->pluck('id'))
            ->whereRaw('id = (SELECT MAX(hs2.id) FROM harvest_stocks hs2 WHERE hs2.harvest_id = harvest_stocks.harvest_id)')
            ->get()
            ->keyBy('harvest_id');

        $this->availableHarvests = $harvests
            ->map(function ($harvest) use ($latestStocks) {
                $latestStock = $latestStocks->get($harvest->id);
                $harvest->available_qty_computed = $latestStock
                    ? (float) $latestStock->available_qty
                    : (float) $harvest->total_weight;

                return $harvest;
            })
            ->filter(fn ($h) => $h->available_qty_computed > 0)
            ->values();
    }

    public function loadLots(): void
    {
        $existingLotIds = collect($this->items)->pluck('wine_lot_id')->filter()->values()->all();

        $this->availableLots = ProductLot::where('user_id', Auth::id())
            ->where('archived', false)
            ->where(function ($q) use ($existingLotIds) {
                $q->where('available_quantity', '>', 0)
                    ->orWhereIn('id', $existingLotIds);
            })
            ->orderBy('name')
            ->get();
    }

    public function updatedSelectedCampaign(): void
    {
        $this->loadHarvests();
        $this->selectedHarvestId = '';
    }

    public function updatedClientId(string $value): void
    {
        if ($value) {
            $client = Client::with([
                'addresses.municipality',
                'addresses.province',
                'addresses.autonomousCommunity',
            ])->find($value);

            if ($client) {
                $primary = $client->addresses->firstWhere('is_default', true)
                    ?? $client->addresses->first();

                if ($primary) {
                    $this->client_address_id = (string) $primary->id;
                } else {
                    $this->client_address_id = '';
                    $this->addError('client_id', __('Este cliente no tiene ninguna dirección configurada. Por favor, añade una dirección al cliente primero.'));
                }

                $this->availableAddresses = $client->addresses;
            } else {
                $this->availableAddresses = collect();
                $this->client_address_id = '';
            }
        } else {
            $this->availableAddresses = collect();
            $this->client_address_id = '';
        }
    }

    public function addHarvestToInvoice(): void
    {
        if (! $this->selectedHarvestId) {
            return;
        }

        $harvest = Harvest::with(['activity.plot', 'plotPlanting.grapeVariety'])
            ->find($this->selectedHarvestId);

        if (! $harvest) {
            $this->toastError(__('Cosecha no encontrada.'));

            return;
        }

        foreach ($this->items as $item) {
            if (isset($item['harvest_id']) && (int) $item['harvest_id'] === $harvest->id) {
                $this->toastError(__('Esta cosecha ya está en la factura actual.'));

                return;
            }
        }

        $latestStock = HarvestStock::where('harvest_id', $harvest->id)->latest('id')->first();
        $availableQty = $latestStock
            ? (float) $latestStock->available_qty
            : (float) $harvest->total_weight;

        if ($availableQty <= 0) {
            $this->toastError(__('Esta cosecha no tiene stock disponible para facturar.'));

            return;
        }

        $user = Auth::user();
        $defaultTax = $user->defaultTax()->first()
            ?? $this->availableTaxes->where('code', 'IVA')->where('rate', 21)->first()
            ?? $this->availableTaxes->first();

        $grapeVarietyName = $harvest->plotPlanting->grapeVariety->name ?? 'Uva';
        $plotName = $harvest->activity->plot->name ?? '';
        $itemName = $grapeVarietyName.($plotName ? ' - '.$plotName : '');

        $this->items[] = [
            'id' => null,
            'harvest_id' => $harvest->id,
            'wine_lot_id' => null,
            'concept_type' => 'harvest',
            'name' => $itemName,
            'description' => __('Cosecha del ').$harvest->harvest_start_date->format('d/m/Y').
                ($harvest->plotPlanting->grapeVariety ? ' - Variedad: '.$harvest->plotPlanting->grapeVariety->name : ''),
            'sku' => __('HARV-').$harvest->id,
            'quantity' => $availableQty,
            'unit' => 'kg',
            'available_qty' => $availableQty,
            'unit_price' => $harvest->price_per_kg ?? 0,
            'discount_percentage' => 0,
            'tax_id' => $defaultTax?->id,
        ];

        $this->selectedHarvestId = '';
        $this->toastSuccess(__('Cosecha añadida a la factura.'));
    }

    public function addWineToInvoice(): void
    {
        if (! $this->selectedLotId) {
            return;
        }

        $lot = ProductLot::where('user_id', Auth::id())->find($this->selectedLotId);

        if (! $lot) {
            $this->toastError(__('Lote no encontrado.'));

            return;
        }

        foreach ($this->items as $item) {
            if (isset($item['wine_lot_id']) && (int) $item['wine_lot_id'] === $lot->id) {
                $this->toastError(__('Este lote ya está en la factura.'));

                return;
            }
        }

        $this->items[] = [
            'id' => null,
            'harvest_id' => null,
            'wine_lot_id' => $lot->id,
            'concept_type' => 'wine',
            'name' => $lot->name.($lot->vintage ? " ({$lot->vintage})" : ''),
            'description' => '',
            'sku' => $lot->sku ?? '',
            'quantity' => 1,
            'unit' => 'botella',
            'available_qty' => (float) $lot->available_quantity,
            'unit_price' => $lot->price_per_unit ? (float) $lot->price_per_unit : 0,
            'discount_percentage' => 0,
            'tax_id' => $this->defaultTaxId ?: null,
        ];

        $this->selectedLotId = '';
        $this->toastSuccess(__('Producto añadido.'));
    }

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'harvest_id' => null,
            'wine_lot_id' => null,
            'concept_type' => 'other',
            'name' => '',
            'description' => '',
            'sku' => '',
            'quantity' => 1,
            'unit' => 'unidades',
            'available_qty' => null,
            'unit_price' => 0,
            'discount_percentage' => 0,
            'tax_id' => $this->defaultTaxId ?: null,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getSubtotalProperty(): float
    {
        return $this->vatTotals()['tax_base'];
    }

    public function getDiscountAmountProperty(): float
    {
        return $this->vatTotals()['discount_amount'];
    }

    public function getTaxAmountProperty(): float
    {
        return $this->vatTotals()['tax_amount'];
    }

    public function getTotalAmountProperty(): float
    {
        return $this->vatTotals()['total'];
    }

    private function vatTotals(): array
    {
        return $this->invoiceService->calculateVatTotals(
            $this->items,
            $this->availableTaxes->keyBy('id'),
        );
    }
}
