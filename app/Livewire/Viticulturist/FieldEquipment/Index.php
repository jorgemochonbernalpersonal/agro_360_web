<?php

namespace App\Livewire\Viticulturist\FieldEquipment;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\FieldEquipment;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public function deactivate(int $id): void
    {
        $this->findOwned(FieldEquipment::class, $id)->update(['active' => false]);
        $this->toastSuccess(__('Equipo dado de baja.'));
    }

    protected function baseQuery(): Builder
    {
        return FieldEquipment::where('viticulturist_id', $this->viticulturistId())->active();
    }

    protected function defaultOrderBy(): array
    {
        return ['name', 'asc'];
    }

    protected function perPage(): int
    {
        return 0;
    }

    protected function viewData(mixed $entries): array
    {
        $base = FieldEquipment::where('viticulturist_id', $this->viticulturistId())->active();

        $stats = [
            'total' => (clone $base)->count(),
            'overdue' => (clone $base)->whereNotNull('next_inspection_date')->where('next_inspection_date', '<', now())->count(),
            'due' => (clone $base)->whereNotNull('next_inspection_date')->whereBetween('next_inspection_date', [now(), now()->addDays(90)])->count(),
            'no_date' => (clone $base)->whereNull('next_inspection_date')->count(),
        ];

        return [
            'equipment' => $entries,
            'types' => FieldEquipment::TYPES,
            'stats' => $stats,
        ];
    }
}
