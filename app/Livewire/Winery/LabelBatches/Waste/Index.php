<?php

namespace App\Livewire\Winery\LabelBatches\Waste;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\LabelBatch;
use App\Models\LabelWaste;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public LabelBatch $labelBatch;

    public function mount(LabelBatch $labelBatch): void
    {
        $this->authorize('update', $labelBatch);
        $this->labelBatch = $labelBatch;
    }

    public function delete(int $id): void
    {
        $waste = LabelWaste::where('label_batch_id', $this->labelBatch->id)->findOrFail($id);

        $qty = $waste->quantity;
        $waste->delete();

        $this->labelBatch->decrement('wasted_quantity', $qty);

        $this->toastSuccess(__('Merma eliminada correctamente.'));
    }

    public function render()
    {
        $wastes = LabelWaste::where('label_batch_id', $this->labelBatch->id)
            ->orderByDesc('waste_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('livewire.winery.label-batches.waste.index', [
            'wastes' => $wastes,
        ])->layout('layouts.app');
    }
}
