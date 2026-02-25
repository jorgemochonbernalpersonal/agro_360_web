<?php

namespace App\Livewire\Viticulturist\CueExports;

use App\Models\CueExport;
use App\Models\Exploitation;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithToastNotifications;

    public bool $showModal = false;
    public ?int $editingId = null;

    public $exploitation_id = '';
    public $campaign_year = '';
    public $period_type = 'annual';
    public $from_date = '';
    public $to_date = '';

    public function mount()
    {
        $this->campaign_year = now()->year;
        $this->from_date = now()->startOfYear()->format('Y-m-d');
        $this->to_date = now()->endOfYear()->format('Y-m-d');
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $export = CueExport::where('viticulturist_id', Auth::id())->findOrFail($id);

        if ($export->status !== 'draft') {
            $this->toastError('Solo se pueden editar exportaciones en estado Borrador.');
            return;
        }

        $this->editingId = $id;
        $this->exploitation_id = $export->exploitation_id;
        $this->campaign_year = $export->campaign_year;
        $this->period_type = $export->period_type;
        $this->from_date = $export->from_date->format('Y-m-d');
        $this->to_date = $export->to_date->format('Y-m-d');
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'exploitation_id' => 'required|exists:exploitations,id',
            'campaign_year'   => 'required|integer|min:2000|max:' . (now()->year + 1),
            'period_type'     => 'required|in:annual,quarterly',
            'from_date'       => 'required|date',
            'to_date'         => 'required|date|after_or_equal:from_date',
        ], [
            'exploitation_id.required' => 'Debes seleccionar una explotación.',
            'campaign_year.required'   => 'El año de campaña es obligatorio.',
            'from_date.required'       => 'La fecha de inicio es obligatoria.',
            'to_date.after_or_equal'   => 'La fecha de fin debe ser igual o posterior a la de inicio.',
        ]);

        $user = Auth::user();

        // Verificar que la explotación pertenece al usuario
        $exploitation = Exploitation::where('viticulturist_id', $user->id)
            ->findOrFail($this->exploitation_id);

        $data = [
            'exploitation_id' => $exploitation->id,
            'viticulturist_id' => $user->id,
            'campaign_year'   => $this->campaign_year,
            'period_type'     => $this->period_type,
            'from_date'       => $this->from_date,
            'to_date'         => $this->to_date,
            'status'          => 'draft',
        ];

        if ($this->editingId) {
            CueExport::where('viticulturist_id', $user->id)
                ->findOrFail($this->editingId)
                ->update($data);
            $this->toastSuccess('Exportación CUE actualizada.');
        } else {
            CueExport::create($data);
            $this->toastSuccess('Exportación CUE creada correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function markAsGenerated(int $id)
    {
        $export = CueExport::where('viticulturist_id', Auth::id())->findOrFail($id);

        if ($export->status !== 'draft') {
            $this->toastError('Solo se pueden marcar como generadas las exportaciones en borrador.');
            return;
        }

        $export->update([
            'status'       => 'generated',
            'generated_at' => now(),
        ]);

        $this->toastSuccess('Exportación marcada como generada.');
    }

    public function markAsSent(int $id)
    {
        $export = CueExport::where('viticulturist_id', Auth::id())->findOrFail($id);

        if ($export->status !== 'generated') {
            $this->toastError('Solo se pueden marcar como enviadas las exportaciones generadas.');
            return;
        }

        $export->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);

        $this->toastSuccess('Exportación marcada como enviada al MAPA.');
    }

    public function delete(int $id)
    {
        $export = CueExport::where('viticulturist_id', Auth::id())->findOrFail($id);

        if ($export->status !== 'draft') {
            $this->toastError('Solo se pueden eliminar exportaciones en estado Borrador.');
            return;
        }

        $export->delete();
        $this->toastSuccess('Exportación eliminada.');
    }

    protected function resetForm()
    {
        $this->editingId = null;
        $this->exploitation_id = '';
        $this->campaign_year = now()->year;
        $this->period_type = 'annual';
        $this->from_date = now()->startOfYear()->format('Y-m-d');
        $this->to_date = now()->endOfYear()->format('Y-m-d');
        $this->resetValidation();
    }

    public function updatedPeriodType($value)
    {
        if ($value === 'annual') {
            $this->from_date = now()->setYear($this->campaign_year)->startOfYear()->format('Y-m-d');
            $this->to_date = now()->setYear($this->campaign_year)->endOfYear()->format('Y-m-d');
        }
    }

    public function updatedCampaignYear($value)
    {
        if ($this->period_type === 'annual' && $value >= 2000) {
            $this->from_date = now()->setYear($value)->startOfYear()->format('Y-m-d');
            $this->to_date = now()->setYear($value)->endOfYear()->format('Y-m-d');
        }
    }

    public function render()
    {
        $user = Auth::user();

        $exports = CueExport::where('viticulturist_id', $user->id)
            ->with('exploitation')
            ->orderByDesc('campaign_year')
            ->orderByDesc('created_at')
            ->get();

        $exploitations = Exploitation::where('viticulturist_id', $user->id)
            ->where('active', true)
            ->orderBy('exploitation_name')
            ->get();

        return view('livewire.viticulturist.cue-exports.index', [
            'exports'       => $exports,
            'exploitations' => $exploitations,
        ]);
    }
}
