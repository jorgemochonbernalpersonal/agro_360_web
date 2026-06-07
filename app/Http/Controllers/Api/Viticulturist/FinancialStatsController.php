<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialStatsController extends BaseApiController
{
    // ─── GET /viticulturist/financial-stats ──────────────────────────────────

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $year = $request->input('year', now()->year);

        $invoicesBase = Invoice::forUser($user->id)
            ->whereYear('invoice_date', $year);

        $totalInvoiced = (clone $invoicesBase)->sum('total_amount');
        $paidAmount = (clone $invoicesBase)->where('payment_status', 'paid')->sum('total_amount');
        $pendingAmount = (clone $invoicesBase)->where('payment_status', 'unpaid')->sum('total_amount');
        $overdueAmount = (clone $invoicesBase)->where('payment_status', 'overdue')->sum('total_amount');
        $invoiceCount = (clone $invoicesBase)->count();
        $collectionRate = $totalInvoiced > 0 ? round(($paidAmount / $totalInvoiced) * 100, 1) : 0;
        $averageInvoice = $invoiceCount > 0 ? round($totalInvoiced / $invoiceCount, 2) : 0;

        $activeClients = Client::forUser($user->id)->active()->count();

        // Monthly income
        $monthlyIncome = Invoice::forUser($user->id)
            ->whereYear('invoice_date', $year)
            ->where('payment_status', 'paid')
            ->select(DB::raw('MONTH(invoice_date) as month'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly[] = round((float) ($monthlyIncome[$m] ?? 0), 2);
        }

        // Growth vs previous year
        $prevTotal = Invoice::forUser($user->id)
            ->whereYear('invoice_date', $year - 1)
            ->sum('total_amount');
        $growthPercentage = $prevTotal > 0
            ? round((($totalInvoiced - $prevTotal) / $prevTotal) * 100, 1)
            : null;

        return $this->success([
            'year' => $year,
            'total_invoiced' => round((float) $totalInvoiced, 2),
            'paid_amount' => round((float) $paidAmount, 2),
            'pending_amount' => round((float) $pendingAmount, 2),
            'overdue_amount' => round((float) $overdueAmount, 2),
            'collection_rate' => $collectionRate,
            'average_invoice' => $averageInvoice,
            'invoice_count' => $invoiceCount,
            'active_clients' => $activeClients,
            'monthly_income' => $monthly,
            'growth_percentage' => $growthPercentage,
        ]);
    }
}
