<?php

namespace App\Livewire\Viticulturist\FieldApplicators;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\FieldApplicator;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public function deactivate(int $id): void
    {
        $this->findOwned(FieldApplicator::class, $id)->update(['active' => false]);
        $this->toastSuccess(__('Aplicador dado de baja.'));
    }

    protected function baseQuery(): Builder
    {
        return FieldApplicator::where('viticulturist_id', $this->viticulturistId())->active();
    }

    protected function defaultOrderBy(): array { return ['name', 'asc']; }

    protected function perPage(): int { return 0; }

    protected function viewData(mixed $entries): array
    {
        $base = FieldApplicator::where('viticulturist_id', $this->viticulturistId())->active();

        $stats = [
            'total'    => (clone $base)->count(),
            'expired'  => (clone $base)->whereNotNull('ropo_expiry_date')->where('ropo_expiry_date', '<', now())->count(),
            'expiring' => (clone $base)->whereNotNull('ropo_expiry_date')->whereBetween('ropo_expiry_date', [now(), now()->addDays(90)])->count(),
            'advisors' => (clone $base)->where('is_advisor', true)->count(),
        ];

        return [
            'applicators' => $entries,
            'categories'  => FieldApplicator::CATEGORIES,
            'stats'       => $stats,
        ];
    }
}
