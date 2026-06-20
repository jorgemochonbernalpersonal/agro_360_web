<?php

namespace App\Livewire\Viticulturist\Invoices\GrapePurchase;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search = '';

    public string $wineryFilter = '';

    public string $paymentFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'wineryFilter' => ['except' => ''],
        'paymentFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingWineryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentFilter(): void
    {
        $this->resetPage();
    }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'wineryFilter' => '', 'paymentFilter' => ''];
    }

    protected function baseQuery(): Builder
    {
        return Invoice::where('viticulturist_id', $this->viticulturistId())
            ->where('invoice_type', 'grape_purchase')
            ->with('user');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(invoice_number,\'\')) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(IFNULL(delivery_note_code,\'\')) LIKE ?', [$term])
                    ->orWhereHas('user', fn ($q2) => $q2->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        if ($this->wineryFilter) {
            $query->where('user_id', $this->wineryFilter);
        }

        if ($this->paymentFilter) {
            $query->where('payment_status', $this->paymentFilter);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['invoice_date', 'desc'];
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('invoice_date')->orderByDesc('id');
    }

    protected function perPage(): int
    {
        return 15;
    }

    protected function viewData(mixed $entries): array
    {
        $viticulturistId = $this->viticulturistId();

        $wineries = User::whereIn('id',
            Invoice::where('viticulturist_id', $viticulturistId)
                ->where('invoice_type', 'grape_purchase')
                ->distinct()
                ->pluck('user_id')
        )->orderBy('name')->get(['id', 'name']);

        $baseQuery = Invoice::where('viticulturist_id', $viticulturistId)
            ->where('invoice_type', 'grape_purchase')
            ->where('status', '!=', 'cancelled');

        $stats = [
            'total_amount' => (clone $baseQuery)->sum('total_amount'),
            'paid_amount' => (clone $baseQuery)->where('payment_status', 'paid')->sum('total_amount'),
            'pending_count' => (clone $baseQuery)->where('payment_status', 'unpaid')->count(),
        ];

        return [
            'invoices' => $entries,
            'wineries' => $wineries,
            'stats' => $stats,
        ];
    }
}
