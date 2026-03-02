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
        $this->toastSuccess('Aplicador dado de baja.');
    }

    protected function baseQuery(): Builder
    {
        return FieldApplicator::where('viticulturist_id', $this->viticulturistId())->active();
    }

    protected function defaultOrderBy(): array { return ['name', 'asc']; }

    protected function perPage(): int { return 0; }

    protected function viewData(mixed $entries): array
    {
        return [
            'applicators' => $entries,
            'categories'  => FieldApplicator::CATEGORIES,
        ];
    }
}
