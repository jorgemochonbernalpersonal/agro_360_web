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
        $this->toastSuccess('Equipo dado de baja.');
    }

    protected function baseQuery(): Builder
    {
        return FieldEquipment::where('viticulturist_id', $this->viticulturistId())->active();
    }

    protected function defaultOrderBy(): array { return ['name', 'asc']; }

    protected function perPage(): int { return 0; }

    protected function viewData(mixed $entries): array
    {
        return [
            'equipment' => $entries,
            'types'     => FieldEquipment::TYPES,
        ];
    }
}
