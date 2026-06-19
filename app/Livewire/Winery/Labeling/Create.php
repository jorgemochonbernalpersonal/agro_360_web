<?php

namespace App\Livewire\Winery\Labeling;

use App\Livewire\Winery\AbstractCreate;
use App\Models\LabelBatch;
use App\Models\Wine;
use App\Models\WineBottling;
use App\Models\WineLabeling;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

/**
 * @property-read mixed $wines
 * @property-read mixed $wineBottlings
 * @property-read mixed $labelBatches
 * @property-read mixed $selectedBatch
 */
class Create extends AbstractCreate
{
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

    protected function performCreate(): void
    {
        Wine::where('user_id', Auth::id())->findOrFail($this->wine_id);

        $error = DB::transaction(function () {
            $batchId = $this->label_batch_id ?: null;
            $qty = (int) $this->quantity_labeled;

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
                'user_id' => $this->ownerId(),
                'wine_id' => $this->wine_id,
                'wine_bottling_id' => $this->wine_bottling_id ?: null,
                'label_batch_id' => $batchId,
                'labeling_date' => $this->labeling_date,
                'quantity_labeled' => $qty,
                'from_number' => $this->from_number ?: null,
                'to_number' => $this->to_number ?: null,
                'notes' => $this->notes ?: null,
                'created_by' => $this->ownerId(),
            ]);

            return null;
        });

        if ($error) {
            throw ValidationException::withMessages(['quantity_labeled' => $error]);
        }
    }

    protected function successMessage(): string
    {
        return __('Sesión de etiquetado registrada.');
    }

    protected function indexRoute(): string
    {
        return 'winery.labeling.index';
    }

    protected function viewData(): array
    {
        return [
            'wines' => $this->wines,
            'wineBottlings' => $this->wineBottlings,
            'labelBatches' => $this->labelBatches,
            'selectedBatch' => $this->selectedBatch,
        ];
    }
}
