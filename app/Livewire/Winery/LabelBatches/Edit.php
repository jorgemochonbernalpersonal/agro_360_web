<?php

namespace App\Livewire\Winery\LabelBatches;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\LabelBatch;
use App\Models\LabelWaste;
use App\Models\Wine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Edit extends Component
{
    use WithOwnershipRules, WithRoleAwareRedirect, WithToastNotifications;

    public LabelBatch $batch;

    // ── Metadatos del lote ────────────────────────────────────────────────────
    public string $name = '';

    public string $wine_id = '';

    public string $source = 'own';

    public string $notes = '';

    // ── Formulario de nueva merma ─────────────────────────────────────────────
    public bool $showWasteForm = false;

    public string $waste_quantity = '';

    public string $waste_from = '';

    public string $waste_to = '';

    public string $waste_reason = '';

    public string $waste_date = '';

    public function mount(LabelBatch $batch): void
    {
        abort_if($batch->user_id !== Auth::id(), 403);

        $this->batch = $batch;
        $this->name = $batch->name;
        $this->wine_id = (string) ($batch->wine_id ?? '');
        $this->source = $batch->source;
        $this->notes = $batch->notes ?? '';
        $this->waste_date = now()->toDateString();
    }

    #[Computed]
    public function wines()
    {
        return Wine::where('user_id', Auth::id())->active()->orderBy('name')->get();
    }

    #[Computed]
    public function wastes()
    {
        return $this->batch->wastes()->get();
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($data['wine_id']) {
            Wine::where('user_id', Auth::id())->findOrFail($data['wine_id']);
        }

        $this->batch->update([
            'wine_id' => $data['wine_id'] ?: null,
            'name' => $data['name'],
            'source' => $data['source'],
            'notes' => $data['notes'] ?: null,
        ]);

        $this->toastSuccess(__('Lote actualizado correctamente.'));
        $this->roleRedirect('label-batches.index');
    }

    public function toggleWasteForm(): void
    {
        $this->showWasteForm = ! $this->showWasteForm;
        $this->resetWasteForm();
    }

    public function saveWaste(): void
    {
        $data = $this->validate($this->wasteRules());

        $qty = (int) $data['waste_quantity'];

        $error = DB::transaction(function () use ($qty, $data) {
            $fresh = LabelBatch::where('id', $this->batch->id)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            if ($qty > $fresh->available_quantity) {
                return "Solo hay {$fresh->available_quantity} etiquetas disponibles.";
            }

            LabelWaste::create([
                'label_batch_id' => $fresh->id,
                'user_id' => Auth::id(),
                'quantity' => $qty,
                'from_number' => $data['waste_from'] ?: null,
                'to_number' => $data['waste_to'] ?: null,
                'reason' => $data['waste_reason'] ?: null,
                'waste_date' => $data['waste_date'],
                'created_by' => Auth::id(),
            ]);

            $fresh->increment('wasted_quantity', $qty);
            $this->batch->refresh();

            return null;
        });

        if ($error) {
            $this->addError('waste_quantity', $error);

            return;
        }

        unset($this->wastes);
        $this->showWasteForm = false;
        $this->resetWasteForm();
        $this->toastSuccess(__('Merma registrada correctamente.'));
    }

    public function deleteWaste(int $wasteId): void
    {
        DB::transaction(function () use ($wasteId) {
            $waste = LabelWaste::whereHas('labelBatch', fn ($q) => $q->where('user_id', Auth::id()))
                ->findOrFail($wasteId);

            $qty = $waste->quantity;
            $waste->delete();

            LabelBatch::where('id', $this->batch->id)->decrement('wasted_quantity', $qty);
            $this->batch->refresh();
        });

        unset($this->wastes);
        $this->toastSuccess(__('Merma eliminada.'));
    }

    public function render()
    {
        return view('livewire.winery.label-batches.edit', [
            'sources' => LabelBatch::SOURCES,
            'wines' => $this->wines,
            'wastes' => $this->wastes,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'wine_id' => $this->ownedWineRule(false),
            'source' => ['required', 'in:own,do_assigned,other'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function wasteRules(): array
    {
        return [
            'waste_quantity' => ['required', 'integer', 'min:1'],
            'waste_from' => ['nullable', 'integer', 'min:1'],
            'waste_to' => ['nullable', 'integer', 'min:1', 'gte:waste_from'],
            'waste_reason' => ['nullable', 'string'],
            'waste_date' => ['required', 'date'],
        ];
    }

    protected function resetWasteForm(): void
    {
        $this->waste_quantity = '';
        $this->waste_from = '';
        $this->waste_to = '';
        $this->waste_reason = '';
        $this->waste_date = now()->toDateString();
    }
}
