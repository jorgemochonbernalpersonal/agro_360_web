<?php

namespace App\Livewire\Winery\Labeling;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\LabelBatch;
use App\Models\Wine;
use App\Models\WineBottling;
use App\Models\WineLabeling;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read mixed $wines
 * @property-read mixed $wineBottlings
 * @property-read mixed $labelBatches
 * @property-read mixed $selectedBatch
 */
class Create extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public string $wine_id = '';

    public string $wine_bottling_id = '';

    public string $label_batch_id = '';

    public string $labeling_date = '';

    public string $quantity_labeled = '';

    public string $from_number = '';

    public string $to_number = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->labeling_date = now()->toDateString();
    }

    #[Computed]
    public function wines()
    {
        return Wine::where('user_id', Auth::id())->active()->orderBy('name')->get();
    }

    #[Computed]
    public function wineBottlings()
    {
        if (! $this->wine_id) {
            return collect();
        }

        return WineBottling::where('user_id', Auth::id())
            ->where('wine_id', $this->wine_id)
            ->orderByDesc('bottling_date')
            ->get();
    }

    #[Computed]
    public function labelBatches()
    {
        return LabelBatch::where('user_id', Auth::id())
            ->where(fn ($q) => $q->whereNull('wine_id')->orWhere('wine_id', $this->wine_id ?: 0))
            ->withStock()
            ->orderByDesc('id')
            ->get();
    }

    #[Computed]
    public function selectedBatch(): ?LabelBatch
    {
        if (! $this->label_batch_id) {
            return null;
        }

        return LabelBatch::find($this->label_batch_id);
    }

    public function updatedWineId(): void
    {
        $this->wine_bottling_id = '';
        unset($this->wineBottlings);
        unset($this->labelBatches);
        unset($this->selectedBatch);
    }

    public function updatedLabelBatchId(): void
    {
        unset($this->selectedBatch);
        // Auto-fill from_number if batch selected and from_number empty
        if ($this->label_batch_id) {
            $batch = LabelBatch::find($this->label_batch_id);
            if ($batch && ! $this->from_number) {
                $nextStart = $batch->start_number + $batch->used_quantity + $batch->wasted_quantity;
                $this->from_number = (string) $nextStart;
            }
        }
    }

    public function updatedQuantityLabeled(): void
    {
        // Auto-fill to_number from from_number + quantity
        if ($this->from_number && $this->quantity_labeled) {
            $this->to_number = (string) ((int) $this->from_number + (int) $this->quantity_labeled - 1);
        }
    }

    public function save(): void
    {
        $data = $this->validate();

        Wine::where('user_id', Auth::id())->findOrFail($data['wine_id']);

        $error = DB::transaction(function () use ($data) {
            $batchId = $data['label_batch_id'] ?: null;
            $qty = (int) $data['quantity_labeled'];

            if ($batchId) {
                $batch = LabelBatch::where('id', $batchId)
                    ->where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($qty > $batch->available_quantity) {
                    return "El lote solo tiene {$batch->available_quantity} etiquetas disponibles.";
                }

                $batch->increment('used_quantity', $qty);
            }

            WineLabeling::create([
                'user_id' => Auth::id(),
                'wine_id' => $data['wine_id'],
                'wine_bottling_id' => $data['wine_bottling_id'] ?: null,
                'label_batch_id' => $batchId,
                'labeling_date' => $data['labeling_date'],
                'quantity_labeled' => $qty,
                'from_number' => $data['from_number'] ?: null,
                'to_number' => $data['to_number'] ?: null,
                'notes' => $data['notes'] ?: null,
                'created_by' => Auth::id(),
            ]);

            return null;
        });

        if ($error) {
            $this->addError('quantity_labeled', $error);

            return;
        }

        $this->toastSuccess(__('Sesión de etiquetado registrada.'));
        $this->roleRedirect('labeling.index');
    }

    public function render()
    {
        return view('livewire.winery.labeling.create', [
            'wines' => $this->wines,
            'wineBottlings' => $this->wineBottlings,
            'labelBatches' => $this->labelBatches,
            'selectedBatch' => $this->selectedBatch,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return [
            'wine_id' => ['required', Rule::exists('wines', 'id')->where('user_id', Auth::id())],
            'wine_bottling_id' => ['nullable', Rule::exists('wine_bottlings', 'id')->where('user_id', Auth::id())],
            'label_batch_id' => ['nullable', Rule::exists('label_batches', 'id')->where('user_id', Auth::id())],
            'labeling_date' => ['required', 'date'],
            'quantity_labeled' => ['required', 'integer', 'min:1'],
            'from_number' => ['nullable', 'integer', 'min:1'],
            'to_number' => ['nullable', 'integer', 'min:1', 'gte:from_number'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
