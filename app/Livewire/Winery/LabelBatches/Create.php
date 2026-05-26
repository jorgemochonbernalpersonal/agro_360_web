<?php

namespace App\Livewire\Winery\LabelBatches;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\LabelBatch;
use App\Models\Wine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications, WithRoleAwareRedirect;

    public string $name         = '';
    public string $wine_id      = '';
    public string $source       = 'own';
    public string $start_number = '';
    public string $end_number   = '';
    public string $notes        = '';

    #[Computed]
    public function wines()
    {
        return Wine::where('user_id', Auth::id())->active()->orderBy('name')->get();
    }

    #[Computed]
    public function totalPreview(): ?int
    {
        if (! $this->start_number || ! $this->end_number) return null;
        $start = (int) $this->start_number;
        $end   = (int) $this->end_number;
        return $end >= $start ? ($end - $start + 1) : null;
    }

    protected function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'wine_id'      => ['nullable', Rule::exists('wines', 'id')->where('user_id', Auth::id())],
            'source'       => ['required', 'in:own,do_assigned,other'],
            'start_number' => ['required', 'integer', 'min:1'],
            'end_number'   => ['required', 'integer', 'min:1', 'gte:start_number'],
            'notes'        => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'end_number.gte' => __('El número final debe ser mayor o igual al número inicial.'),
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($data['wine_id']) {
            Wine::where('user_id', Auth::id())->findOrFail($data['wine_id']);
        }

        $start = (int) $data['start_number'];
        $end   = (int) $data['end_number'];

        LabelBatch::create([
            'user_id'        => Auth::id(),
            'wine_id'        => $data['wine_id'] ?: null,
            'name'           => $data['name'],
            'source'         => $data['source'],
            'start_number'   => $start,
            'end_number'     => $end,
            'total_quantity' => $end - $start + 1,
            'used_quantity'  => 0,
            'wasted_quantity'=> 0,
            'notes'          => $data['notes'] ?: null,
        ]);

        $this->toastSuccess(__('Lote de etiquetas creado correctamente.'));
        $this->roleRedirect('label-batches.index');
    }

    public function render()
    {
        return view('livewire.winery.label-batches.create', [
            'sources'      => LabelBatch::SOURCES,
            'wines'        => $this->wines,
            'totalPreview' => $this->totalPreview,
        ])->layout('layouts.app');
    }
}
