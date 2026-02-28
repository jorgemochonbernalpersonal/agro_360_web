<?php

namespace App\Livewire\Viticulturist\Almacen;

use App\Models\Campaign;
use App\Models\PhytosanitaryProduct;
use App\Models\ProductStock;
use App\Models\Supply;
use App\Models\SupplyPurchase;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    #[Url(as: 'tab', except: 'fitosanitarios')]
    public string $tab = 'fitosanitarios';

    // =========================================================
    // Tab: Fitosanitarios (Inventory)
    // =========================================================
    public string $inv_search    = '';
    public string $inv_product   = '';
    public string $inv_warehouse = '';
    public string $inv_status    = 'all';

    // =========================================================
    // Tab: Insumos (Supplies)
    // =========================================================
    public string $sup_search = '';
    public string $sup_type   = '';
    public bool   $sup_low    = false;

    // Purchase form
    public ?int   $purchaseSupplyId   = null;
    public string $purchaseSupplyName = '';
    public string $p_invoice_date     = '';
    public string $p_invoice_number   = '';
    public string $p_quantity         = '';
    public string $p_unit_of_measurement = 'L';
    public string $p_price_per_unit   = '';
    public string $p_total_cost       = '';
    public string $p_supplier_name    = '';
    public string $p_campaign_id      = '';
    public string $p_notes            = '';

    // =========================================================
    // Tab: Almacenes (Warehouses)
    // =========================================================
    public string $wh_tab    = 'active';
    public string $wh_search = '';

    protected $queryString = [
        'inv_search'    => ['except' => ''],
        'inv_product'   => ['except' => ''],
        'inv_warehouse' => ['except' => ''],
        'inv_status'    => ['except' => 'all'],
        'sup_search'    => ['except' => ''],
        'sup_type'      => ['except' => ''],
        'wh_tab'        => ['except' => 'active'],
        'wh_search'     => ['except' => ''],
    ];

    public function mount(): void
    {
        if (!Auth::user()->isViticulturist()) {
            abort(403);
        }

        $campaign = Campaign::getOrCreateActiveForYear(Auth::id());
        $this->p_campaign_id = (string) ($campaign?->id ?? '');
        $this->p_invoice_date = now()->format('Y-m-d');
    }

    // ── Lifecycle hooks ───────────────────────────────────────

    public function updatingTab(): void        { $this->resetPage(); }
    public function updatingInvSearch(): void   { $this->resetPage(); }
    public function updatingInvProduct(): void  { $this->resetPage(); }
    public function updatingInvWarehouse(): void { $this->resetPage(); }
    public function updatingInvStatus(): void   { $this->resetPage(); }
    public function updatingSupSearch(): void   { $this->resetPage(); }
    public function updatingSupType(): void     { $this->resetPage(); }
    public function updatingSupLow(): void      { $this->resetPage(); }
    public function updatingWhSearch(): void    { $this->resetPage(); }

    // ── Fitosanitarios methods ────────────────────────────────

    public function clearInventoryFilters(): void
    {
        $this->inv_search    = '';
        $this->inv_product   = '';
        $this->inv_warehouse = '';
        $this->inv_status    = 'all';
        $this->resetPage();
    }

    // ── Insumos filter methods ────────────────────────────────

    public function clearInsumoFilters(): void
    {
        $this->sup_search = '';
        $this->sup_type   = '';
        $this->sup_low    = false;
        $this->resetPage();
    }

    // ── Almacenes methods ─────────────────────────────────────

    public function switchWhTab(string $tab): void
    {
        $this->wh_tab = $tab;
        $this->resetPage();
    }

    public function toggleActive(int $warehouseId): void
    {
        $warehouse = Warehouse::where('user_id', Auth::id())->findOrFail($warehouseId);
        $warehouse->update(['active' => !$warehouse->active]);

        $this->toastSuccess($warehouse->active
            ? 'Almacén activado exitosamente.'
            : 'Almacén desactivado exitosamente.'
        );

        if ($warehouse->active && $this->wh_tab === 'inactive') {
            $this->wh_tab = 'active';
        } elseif (!$warehouse->active && $this->wh_tab === 'active') {
            $this->wh_tab = 'inactive';
        }
    }

    public function deleteWarehouse(int $warehouseId): void
    {
        $warehouse = Warehouse::where('user_id', Auth::id())
            ->withCount(['stocks', 'supplies'])
            ->findOrFail($warehouseId);

        if ($warehouse->stocks_count > 0 || $warehouse->supplies_count > 0) {
            $this->toastError('No se puede eliminar el almacén porque tiene productos o insumos asociados.');
            return;
        }

        $warehouse->delete();
        $this->toastSuccess('Almacén eliminado exitosamente.');
    }

    // ── Fitosanitarios actions ────────────────────────────────

    public function deactivateStock(int $id): void
    {
        $stock = ProductStock::where('user_id', Auth::id())->findOrFail($id);
        $stock->update(['active' => false]);
        $this->toastSuccess('Lote archivado.');
    }

    // ── Insumos methods ───────────────────────────────────────

    public function deactivateSupply(int $id): void
    {
        Supply::where('viticulturist_id', Auth::id())->findOrFail($id)->update(['active' => false]);
        $this->toastSuccess('Insumo archivado.');
    }

    public function openPurchase(int $supplyId): void
    {
        $supply = Supply::where('viticulturist_id', Auth::id())->findOrFail($supplyId);
        $this->purchaseSupplyId      = $supplyId;
        $this->purchaseSupplyName    = $supply->name;
        $this->p_unit_of_measurement = $supply->unit_of_measurement;
        $this->resetPurchaseForm();
        $this->js("window.dispatchEvent(new CustomEvent('open-modal', { detail: 'supply-purchase' }))");
    }

    public function updatedPQuantity(mixed $v): void
    {
        if ($v && $this->p_price_per_unit) {
            $this->p_total_cost = (string) round($v * $this->p_price_per_unit, 2);
        }
    }

    public function updatedPPricePerUnit(mixed $v): void
    {
        if ($v && $this->p_quantity) {
            $this->p_total_cost = (string) round($this->p_quantity * $v, 2);
        }
    }

    public function savePurchase(): void
    {
        $this->validate($this->purchaseRules());

        SupplyPurchase::create([
            'supply_id'           => $this->purchaseSupplyId,
            'viticulturist_id'    => Auth::id(),
            'campaign_id'         => $this->p_campaign_id ?: null,
            'invoice_date'        => $this->p_invoice_date,
            'invoice_number'      => $this->p_invoice_number ?: null,
            'quantity'            => $this->p_quantity,
            'unit_of_measurement' => $this->p_unit_of_measurement,
            'price_per_unit'      => $this->p_price_per_unit ?: null,
            'total_cost'          => $this->p_total_cost ?: null,
            'supplier_name'       => $this->p_supplier_name ?: null,
            'notes'               => $this->p_notes ?: null,
        ]);

        $this->toastSuccess('Compra registrada. Stock actualizado.');
        $this->js("window.dispatchEvent(new CustomEvent('close-modal', { detail: 'supply-purchase' }))");
        $this->resetPurchaseForm();
    }

    protected function purchaseRules(): array
    {
        return [
            'p_invoice_date'        => 'required|date',
            'p_quantity'            => 'required|numeric|min:0.001',
            'p_unit_of_measurement' => 'required|exists:units,symbol',
            'p_price_per_unit'      => 'nullable|numeric|min:0',
            'p_total_cost'          => 'nullable|numeric|min:0',
            'p_supplier_name'       => 'nullable|string|max:255',
        ];
    }

    protected function resetPurchaseForm(): void
    {
        $this->p_invoice_date      = now()->format('Y-m-d');
        $this->p_invoice_number    = '';
        $this->p_quantity          = '';
        $this->p_price_per_unit    = '';
        $this->p_total_cost        = '';
        $this->p_supplier_name     = '';
        $this->p_notes             = '';
        $this->resetValidation();
    }

    // ── Render ────────────────────────────────────────────────

    public function render()
    {
        $user     = Auth::user();
        $viewData = [];

        if ($this->tab === 'fitosanitarios') {
            $query = ProductStock::where('user_id', $user->id)
                ->where('active', true)
                ->with(['product', 'warehouse']);

            if ($this->inv_search) {
                $query->whereHas('product', fn ($q) => $q->where('name', 'like', '%' . $this->inv_search . '%'));
            }
            if ($this->inv_product) {
                $query->where('product_id', $this->inv_product);
            }
            if ($this->inv_warehouse) {
                $query->where('warehouse_id', $this->inv_warehouse);
            }
            if ($this->inv_status === 'expiring') {
                $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '>', now())
                    ->where('expiry_date', '<=', now()->addDays(30));
            } elseif ($this->inv_status === 'expired') {
                $query->whereNotNull('expiry_date')->where('expiry_date', '<=', now());
            } elseif ($this->inv_status === 'low_stock') {
                $query->where(fn ($q) => $q->whereColumn('quantity', '<', DB::raw('COALESCE(minimum_stock, 5)')));
            }

            $stocks = $query->orderBy('expiry_date')->orderBy('product_id')->paginate(20);

            $base = fn () => ProductStock::where('user_id', $user->id)->where('active', true);
            $stats = [
                'total_products'      => $base()->distinct('product_id')->count('product_id'),
                'total_value'         => $base()->sum(DB::raw('quantity * COALESCE(unit_price, 0)')),
                'low_stock_count'     => $base()->where(fn ($q) => $q->whereColumn('quantity', '<', DB::raw('COALESCE(minimum_stock, 5)')))->count(),
                'expiring_soon_count' => $base()->whereNotNull('expiry_date')->where('expiry_date', '>', now())->where('expiry_date', '<=', now()->addDays(30))->count(),
            ];

            $viewData = [
                'stocks'         => $stocks,
                'stats'          => $stats,
                'inv_products'   => PhytosanitaryProduct::forUser($user->id)->select(['id', 'name'])->where('active', true)->orderBy('name')->get(),
                'inv_warehouses' => Warehouse::select(['id', 'name'])->where('user_id', $user->id)->where('active', true)->get(),
            ];
        } elseif ($this->tab === 'insumos') {
            $query = Supply::where('viticulturist_id', $user->id)->active();
            if ($this->sup_search) {
                $query->where(fn ($q) => $q->where('name', 'like', '%' . $this->sup_search . '%')
                    ->orWhere('commercial_name', 'like', '%' . $this->sup_search . '%'));
            }
            if ($this->sup_type) {
                $query->where('supply_type', $this->sup_type);
            }
            if ($this->sup_low) {
                $query->lowStock();
            }

            $viewData = [
                'supplies' => $query->with('warehouse')->orderBy('name')->paginate(20),
            ];
        } elseif ($this->tab === 'almacenes') {
            $query = Warehouse::where('user_id', $user->id)
                ->where('active', $this->wh_tab === 'active');

            if ($this->wh_search) {
                $query->where(fn ($q) => $q->where('name', 'like', '%' . $this->wh_search . '%')
                    ->orWhere('location', 'like', '%' . $this->wh_search . '%'));
            }

            $baseWh = Warehouse::where('user_id', $user->id);
            $viewData = [
                'warehouses' => $query->withCount(['stocks', 'supplies'])->orderBy('name')->paginate(12),
                'wh_stats'   => [
                    'total'    => (clone $baseWh)->count(),
                    'active'   => (clone $baseWh)->where('active', true)->count(),
                    'inactive' => (clone $baseWh)->where('active', false)->count(),
                ],
            ];
        }

        // Siempre disponibles (necesarios para los modales de insumos fuera del @elseif)
        $viewData['supplyTypes'] = Supply::SUPPLY_TYPES;
        $viewData['campaigns']   = Campaign::forViticulturist($user->id)->orderByDesc('year')->get();
        $viewData['units']       = Unit::active()->orderBy('category')->orderBy('name')->get();

        return view('livewire.viticulturist.almacen.index', $viewData)
            ->layout('layouts.app', ['title' => 'Almacén de Insumos - Agro365']);
    }
}
