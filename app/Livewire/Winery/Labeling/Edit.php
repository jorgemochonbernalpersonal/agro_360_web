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
 */
class Edit extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

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
        abort_if($labeling->user_id !== Auth::id(), 403);

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

    public function save(): void
    {
        $data = $this->validate();

        Wine::where('user_id', Auth::id())->findOrFail($data['wine_id']);

        $newQty = (int) $data['quantity_labeled'];
        $oldQty = $this->labeling->quantity_labeled;
        $oldBatch = $this->labeling->label_batch_id;
        $newBatch = $data['label_batch_id'] ?: null;

        $error = DB::transaction(function () use ($data, $newQty, $oldQty, $oldBatch, $newBatch) {
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

                $availableAfterReverse = $batch->available_quantity
                    + ($oldBatch === $newBatch ? $oldQty : 0);

                if ($newQty > $availableAfterReverse) {
                    return "El lote solo tiene {$availableAfterReverse} etiquetas disponibles.";
                }

                LabelBatch::where('id', $newBatch)->increment('used_quantity', $newQty);
            }

            $this->labeling->update([
                'wine_id' => $data['wine_id'],
                'wine_bottling_id' => $data['wine_bottling_id'] ?: null,
                'label_batch_id' => $newBatch,
                'labeling_date' => $data['labeling_date'],
                'quantity_labeled' => $newQty,
                'from_number' => $data['from_number'] ?: null,
                'to_number' => $data['to_number'] ?: null,
                'notes' => $data['notes'] ?: null,
            ]);

            return null;
        });

        if ($error) {
            $this->addError('quantity_labeled', $error);

            return;
        }

        $this->toastSuccess(__('Sesión de etiquetado actualizada.'));
        $this->roleRedirect('labeling.index');
    }

    public function render()
    {
        return view('livewire.winery.labeling.edit', [
            'wines' => $this->wines,
            'wineBottlings' => $this->wineBottlings,
            'labelBatches' => $this->labelBatches,
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
