<?php

namespace App\Http\Controllers\Viticulturist;

use App\Exports\InventoryExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class InventoryExportController extends Controller
{
    public function __invoke()
    {
        $export = new InventoryExport(auth()->id());

        return Excel::download($export, 'inventario_' . now()->format('Y-m-d') . '.xlsx');
    }
}
