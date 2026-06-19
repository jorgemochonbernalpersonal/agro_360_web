<?php

namespace App\Livewire\Concerns;

use App\Models\Supply;
use App\Models\SupplyPurchase;
use Illuminate\Support\Facades\Auth;

trait WithSupplyPurchase
{
    public ?int $purchaseSupplyId = null;

    public string $purchaseSupplyName = '';

    public string $p_invoice_date = '';

    public string $p_invoice_number = '';

    public string $p_quantity = '';

    public string $p_unit_of_measurement = 'L';

    public string $p_price_per_unit = '';

    public string $p_total_cost = '';

    public string $p_supplier_name = '';

    public string $p_campaign_id = '';

    public string $p_notes = '';

    public function openPurchase(int $supplyId): void
    {
        $supply = Supply::where('viticulturist_id', Auth::id())->findOrFail($supplyId);
        $this->purchaseSupplyId = $supplyId;
        $this->purchaseSupplyName = $supply->name;
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
            'supply_id' => $this->purchaseSupplyId,
            'viticulturist_id' => Auth::id(),
            'campaign_id' => $this->p_campaign_id ?: null,
            'invoice_date' => $this->p_invoice_date,
            'invoice_number' => $this->p_invoice_number ?: null,
            'quantity' => $this->p_quantity,
            'unit_of_measurement' => $this->p_unit_of_measurement,
            'price_per_unit' => $this->p_price_per_unit ?: null,
            'total_cost' => $this->p_total_cost ?: null,
            'supplier_name' => $this->p_supplier_name ?: null,
            'notes' => $this->p_notes ?: null,
        ]);

        $this->toastSuccess(__('Compra registrada. Stock actualizado.'));
        $this->js("window.dispatchEvent(new CustomEvent('close-modal', { detail: 'supply-purchase' }))");
        $this->resetPurchaseForm();
    }

    protected function purchaseRules(): array
    {
        return [
            'p_invoice_date' => 'required|date',
            'p_quantity' => 'required|numeric|min:0.001',
            'p_unit_of_measurement' => 'required|exists:units,symbol',
            'p_price_per_unit' => 'nullable|numeric|min:0',
            'p_total_cost' => 'nullable|numeric|min:0',
            'p_supplier_name' => 'nullable|string|max:255',
        ];
    }

    protected function resetPurchaseForm(): void
    {
        $this->p_invoice_date = now()->format('Y-m-d');
        $this->p_invoice_number = '';
        $this->p_quantity = '';
        $this->p_price_per_unit = '';
        $this->p_total_cost = '';
        $this->p_supplier_name = '';
        $this->p_notes = '';
        $this->resetValidation();
    }
}
