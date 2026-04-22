<?php

namespace App\Livewire\Supervisor\Documents;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\DoDocument;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    use WithToastNotifications;

    public string $activeTab = 'active';

    // ── Create / Edit form ────────────────────────────────────────────────────
    public bool    $showModal   = false;
    public ?int    $editingId   = null;
    public string  $formType    = DoDocument::TYPE_PLIEGO;
    public string  $formTitle   = '';
    public string  $formVersion = '';
    public string  $formDate    = '';
    public string  $formContent = '';

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ── Modal ─────────────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->editingId   = null;
        $this->formType    = DoDocument::TYPE_PLIEGO;
        $this->formTitle   = '';
        $this->formVersion = '';
        $this->formDate    = '';
        $this->formContent = '';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $doc = DoDocument::forSupervisor(Auth::id())->findOrFail($id);

        if ($doc->status !== DoDocument::STATUS_DRAFT) {
            $this->toastError('Solo se pueden editar documentos en borrador.');
            return;
        }

        $this->editingId   = $doc->id;
        $this->formType    = $doc->type;
        $this->formTitle   = $doc->title;
        $this->formVersion = $doc->version ?? '';
        $this->formDate    = $doc->effective_date?->format('Y-m-d') ?? '';
        $this->formContent = $doc->content ?? '';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        $this->validate([
            'formType'    => ['required', 'in:' . DoDocument::TYPE_PLIEGO . ',' . DoDocument::TYPE_REGLAMENTO],
            'formTitle'   => ['required', 'string', 'max:255'],
            'formVersion' => ['nullable', 'string', 'max:50'],
            'formDate'    => ['nullable', 'date'],
            'formContent' => ['nullable', 'string'],
        ], [
            'formType.required'  => 'Selecciona el tipo de documento.',
            'formTitle.required' => 'El título es obligatorio.',
            'formDate.date'      => 'La fecha de entrada en vigor no es válida.',
        ]);

        $data = [
            'supervisor_id'  => Auth::id(),
            'type'           => $this->formType,
            'title'          => $this->formTitle,
            'version'        => $this->formVersion ?: null,
            'effective_date' => $this->formDate ?: null,
            'content'        => $this->formContent ?: null,
            'status'         => DoDocument::STATUS_DRAFT,
        ];

        if ($this->editingId) {
            $doc = DoDocument::forSupervisor(Auth::id())->findOrFail($this->editingId);
            if ($doc->status !== DoDocument::STATUS_DRAFT) {
                $this->toastError('Solo se pueden editar documentos en borrador.');
                return;
            }
            $doc->update($data);
            $this->toastSuccess('Documento actualizado.');
        } else {
            DoDocument::create($data);
            $this->toastSuccess('Documento creado como borrador.');
        }

        $this->showModal  = false;
        $this->activeTab  = 'draft';
    }

    // ── Publish ───────────────────────────────────────────────────────────────

    public function publish(int $id): void
    {
        $doc = DoDocument::forSupervisor(Auth::id())->findOrFail($id);

        if ($doc->status !== DoDocument::STATUS_DRAFT) {
            $this->toastError('Solo se pueden publicar documentos en borrador.');
            return;
        }

        // Archive any currently active document of the same type
        DoDocument::forSupervisor(Auth::id())
            ->ofType($doc->type)
            ->where('status', DoDocument::STATUS_ACTIVE)
            ->update(['status' => DoDocument::STATUS_ARCHIVED]);

        $doc->update(['status' => DoDocument::STATUS_ACTIVE]);
        $this->toastSuccess('Documento publicado y visible para los viticultores.');
        $this->activeTab = 'active';
    }

    // ── Archive ───────────────────────────────────────────────────────────────

    public function archive(int $id): void
    {
        $doc = DoDocument::forSupervisor(Auth::id())->findOrFail($id);

        if ($doc->status !== DoDocument::STATUS_ACTIVE) {
            $this->toastError('Solo se pueden archivar documentos vigentes.');
            return;
        }

        $doc->update(['status' => DoDocument::STATUS_ARCHIVED]);
        $this->toastSuccess('Documento archivado.');
        $this->activeTab = 'archived';
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function delete(int $id): void
    {
        $doc = DoDocument::forSupervisor(Auth::id())->findOrFail($id);

        if ($doc->status !== DoDocument::STATUS_DRAFT) {
            $this->toastError('Solo se pueden eliminar documentos en borrador.');
            return;
        }

        $doc->delete();
        $this->toastSuccess('Documento eliminado.');
    }

    // ── Render ────────────────────────────────────────────────────────────────

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $documents = DoDocument::forSupervisor($doId)
            ->where('status', $this->activeTab)
            ->orderByDesc('updated_at')
            ->get();

        $counts = [
            'draft'    => DoDocument::forSupervisor($doId)->where('status', DoDocument::STATUS_DRAFT)->count(),
            'active'   => DoDocument::forSupervisor($doId)->where('status', DoDocument::STATUS_ACTIVE)->count(),
            'archived' => DoDocument::forSupervisor($doId)->where('status', DoDocument::STATUS_ARCHIVED)->count(),
        ];

        return view('livewire.supervisor.documents.index', [
            'documents' => $documents,
            'counts'    => $counts,
        ]);
    }
}
