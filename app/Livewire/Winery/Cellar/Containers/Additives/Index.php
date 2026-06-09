<?php

namespace App\Livewire\Winery\Cellar\Containers\Additives;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\ContainerAdditiveSupply;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public Container $container;

    public function mount(Container $container): void
    {
        $this->authorize('view', $container);
        $this->container = $container;
    }

    public function delete(int $id): void
    {
        ContainerAdditiveSupply::where('container_id', $this->container->id)
            ->findOrFail($id)
            ->delete();
        $this->toastSuccess(__('Aditivo eliminado.'));
    }

    public function render()
    {
        $additives = ContainerAdditiveSupply::where('container_id', $this->container->id)
            ->with(['winerySupply', 'unitOfMeasurement', 'creator'])
            ->orderByDesc('additive_date')
            ->paginate(20);

        return view('livewire.winery.cellar.containers.additives.index', [
            'additives' => $additives,
        ])->layout('layouts.app');
    }
}
