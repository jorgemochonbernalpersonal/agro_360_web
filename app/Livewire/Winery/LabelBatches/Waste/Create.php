<?php

namespace App\Livewire\Winery\LabelBatches\Waste;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\LabelBatch;
use App\Models\LabelWaste;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public LabelBatch $labelBatch;

    public string $quantity = '';

    public string $from_number = '';

    public string $to_number = '';

    public string $waste_date = '';

    public string $reason = '';

    public function mount(LabelBatch $labelBatch): void
    {
        abort_if($labelBatch->user_id !== Auth::id(), 403);
        $this->labelBatch = $labelBatch;
        $this->waste_date = now()->toDateString();
    }

    public function save(): void
    {
        $this->validate();

        $available = $this->labelBatch->total_quantity
            - $this->labelBatch->used_quantity
            - $this->labelBatch->wasted_quantity;

        if ((int) $this->quantity > $available) {
            $this->addError('quantity', __('Solo quedan :available etiquetas disponibles.', ['available' => $available]));

            return;
        }

        LabelWaste::create([
            'label_batch_id' => $this->labelBatch->id,
            'user_id' => Auth::id(),
            'quantity' => (int) $this->quantity,
            'from_number' => $this->from_number ?: null,
            'to_number' => $this->to_number ?: null,
            'waste_date' => $this->waste_date,
            'reason' => $this->reason ?: null,
            'created_by' => Auth::id(),
        ]);

        $this->labelBatch->increment('wasted_quantity', (int) $this->quantity);

        $this->toastSuccess(__('Merma de etiquetas registrada correctamente.'));
        $this->roleRedirect('label-batches.waste.index', $this->labelBatch);
    }

    public function render()
    {
        return view('livewire.winery.label-batches.waste.create')
            ->layout('layouts.app');
    }

    protected function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
            'from_number' => ['nullable', 'integer', 'min:0'],
            'to_number' => ['nullable', 'integer', 'min:0', 'gte:from_number'],
            'waste_date' => ['required', 'date'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
