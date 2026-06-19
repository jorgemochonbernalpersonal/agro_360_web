<?php

namespace App\Livewire\Winery\Labeling;

use App\Livewire\Winery\AbstractEdit;
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
 */
class Edit extends AbstractEdit
{
    public WineLabeling $labeling;

    public string $wine_id = '';

    public string $wine_bottling_id = '';

    public string $label_batch_id = '';

    public string $labeling_date = '';

    public string $quantity_labeled = '';

    public string $from_number = '';

    public string $to_number = '';

    public string $notes = '';

    public function mount(WineLabeling $labeling): void
    {
        $this->authorize('update', $labeling);

        $this->labeling = $labeling;

        $this->wine_id = (string) $labeling->wine_id;
        $this->wine_bottling_id = (string) ($labeling->wine_bottling_id ?? '');
        $this->label_batch_id = (string) ($labeling->label_batch_id ?? '');
        $this->labeling_date = $labeling->labeling_date->toDateString();
        $this->quantity_labeled = (string) $labeling->quantity_labeled;
        $this->from_number = (string) ($labeling->from_number ?? '');
        $this->to_number = (string) ($labeling->to_number ?? '');
        $this->notes = $labeling->notes ?? '';
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
        // Show current batch even if empty, plus batches with stock
        return LabelBatch::where('user_id', Auth::id())
            ->where(fn ($q) => $q->whereNull('wine_id')->orWhere('wine_id', $this->wine_id ?: 0))
            ->where(fn ($q) => $q
                ->where('id', $this->labeling->label_batch_id ?: 0)
                ->orWhereRaw('(total_quantity - used_quantity - wasted_quantity) > 0'))
            ->orderByDesc('id')
            ->get();
    }

    public function updatedWineId(): void
    {
        $this->wine_bottling_id = '';
        unset($this->wineBottlings);
        unset($this->labelBatches);
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

    protected function performUpdate(): void
    {
        Wine::where('user_id', Auth::id())->findOrFail($this->wine_id);

        $newQty = (int) $this->quantity_labeled;
        $oldQty = $this->labeling->quantity_labeled;
        $oldBatch = $this->labeling->label_batch_id;
        $newBatch = $this->label_batch_id ?: null;

        $error = DB::transaction(function () use ($newQty, $oldQty, $oldBatch, $newBatch) {
            // Reverse old batch consumption
            if ($oldBatch) {
                LabelBatch::where('id', $oldBatch)->decrement('used_quantity', $oldQty);
            }

            // Apply new batch consumption
            if ($newBatch) {
                $batch = LabelBatch::where('id', $newBatch)
                    ->where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->firstOrFail();

                // Si es el mismo lote, el decrement previo ya devolvió oldQty al
                // stock, por lo que available_quantity (releído fresco) ya refleja
                // la reversión — no hay que sumarlo otra vez.
                $availableAfterReverse = $batch->available_quantity;

                if ($newQty > $availableAfterReverse) {
                    return "El lote solo tiene {$availableAfterReverse} etiquetas disponibles.";
                }

                LabelBatch::where('id', $newBatch)->increment('used_quantity', $newQty);
            }

            $this->labeling->update([
                'wine_id' => $this->wine_id,
                'wine_bottling_id' => $this->wine_bottling_id ?: null,
                'label_batch_id' => $newBatch,
                'labeling_date' => $this->labeling_date,
                'quantity_labeled' => $newQty,
                'from_number' => $this->from_number ?: null,
                'to_number' => $this->to_number ?: null,
                'notes' => $this->notes ?: null,
            ]);

            return null;
        });

        if ($error) {
            throw ValidationException::withMessages(['quantity_labeled' => $error]);
        }
    }

    protected function successMessage(): string
    {
        return __('Sesión de etiquetado actualizada.');
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
        ];
    }
}
