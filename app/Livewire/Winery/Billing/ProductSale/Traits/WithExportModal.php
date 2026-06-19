<?php

namespace App\Livewire\Winery\Billing\ProductSale\Traits;

use Illuminate\Support\Facades\Auth;

trait WithExportModal
{
    public bool $exportModal = false;

    public string $exportDateFrom = '';

    public string $exportDateTo = '';

    public function openExportModal(): void
    {
        $this->exportDateFrom = now()->startOfMonth()->toDateString();
        $this->exportDateTo = now()->toDateString();
        $this->exportModal = true;
    }

    public function closeExportModal(): void
    {
        $this->exportModal = false;
        $this->resetValidation();
    }

    public function export()
    {
        $this->validate(
            [
                'exportDateFrom' => 'required|date',
                'exportDateTo' => 'required|date|after_or_equal:exportDateFrom',
            ],
            [
                'exportDateTo.after_or_equal' => __('La fecha final no puede ser anterior a la inicial.'),
            ]
        );

        $this->closeExportModal();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProductSaleInvoiceExport(Auth::id(), $this->exportDateFrom, $this->exportDateTo),
            'facturas_venta_'.$this->exportDateFrom.'_'.$this->exportDateTo.'.xlsx'
        );
    }
}
