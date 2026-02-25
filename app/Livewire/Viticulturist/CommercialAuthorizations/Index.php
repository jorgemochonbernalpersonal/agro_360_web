<?php

namespace App\Livewire\Viticulturist\CommercialAuthorizations;

use App\Models\CommercialAuthorization;
use App\Models\Exploitation;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithToastNotifications;

    public $filterType = '';

    // Form
    public bool $showModal = false;
    public ?int $editingId = null;

    public $exploitation_id = '';
    public $authorization_type = 'do_registration';
    public $authorization_code = '';
    public $description = '';
    public $issuing_body = '';
    public $issue_date = '';
    public $expiry_date = '';
    public $document_file = '';
    public $notes = '';

    public function mount()
    {
        $this->issue_date = now()->format('Y-m-d');
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $entry = CommercialAuthorization::where('viticulturist_id', Auth::id())->findOrFail($id);
        $this->editingId = $id;
        $this->exploitation_id = $entry->exploitation_id ?? '';
        $this->authorization_type = $entry->authorization_type;
        $this->authorization_code = $entry->authorization_code ?? '';
        $this->description = $entry->description ?? '';
        $this->issuing_body = $entry->issuing_body ?? '';
        $this->issue_date = $entry->issue_date->format('Y-m-d');
        $this->expiry_date = $entry->expiry_date?->format('Y-m-d') ?? '';
        $this->document_file = $entry->document_file ?? '';
        $this->notes = $entry->notes ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate($this->rules());
        $user = Auth::user();

        $data = [
            'viticulturist_id'   => $user->id,
            'exploitation_id'    => $this->exploitation_id ?: null,
            'authorization_type' => $this->authorization_type,
            'authorization_code' => $this->authorization_code ?: null,
            'description'        => $this->description ?: null,
            'issuing_body'       => $this->issuing_body ?: null,
            'issue_date'         => $this->issue_date,
            'expiry_date'        => $this->expiry_date ?: null,
            'document_file'      => $this->document_file ?: null,
            'notes'              => $this->notes ?: null,
            'active'             => true,
        ];

        if ($this->editingId) {
            CommercialAuthorization::where('viticulturist_id', $user->id)->findOrFail($this->editingId)->update($data);
            $this->toastSuccess('Autorización actualizada.');
        } else {
            CommercialAuthorization::create($data);
            $this->toastSuccess('Autorización registrada correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function deactivate(int $id)
    {
        CommercialAuthorization::where('viticulturist_id', Auth::id())->findOrFail($id)->update(['active' => false]);
        $this->toastSuccess('Autorización archivada.');
    }

    protected function rules(): array
    {
        return [
            'authorization_type' => 'required|in:' . implode(',', array_keys(CommercialAuthorization::AUTHORIZATION_TYPES)),
            'issue_date'         => 'required|date',
            'expiry_date'        => 'nullable|date|after_or_equal:issue_date',
            'exploitation_id'    => 'nullable|exists:exploitations,id',
            'authorization_code' => 'nullable|string|max:100',
            'description'        => 'nullable|string|max:255',
            'issuing_body'       => 'nullable|string|max:255',
            'document_file'      => 'nullable|string|max:500',
            'notes'              => 'nullable|string',
        ];
    }

    protected function resetForm()
    {
        $this->editingId = null;
        $this->exploitation_id = '';
        $this->authorization_type = 'do_registration';
        $this->authorization_code = '';
        $this->description = '';
        $this->issuing_body = '';
        $this->issue_date = now()->format('Y-m-d');
        $this->expiry_date = '';
        $this->document_file = '';
        $this->notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();

        $query = CommercialAuthorization::where('viticulturist_id', $user->id)->active();
        if ($this->filterType) {
            $query->where('authorization_type', $this->filterType);
        }

        $entries = $query->orderBy('expiry_date')->paginate(20);
        $exploitations = Exploitation::forViticulturist($user->id)->active()->get();

        // Alertas de vencimiento próximo (60 días)
        $expiring = CommercialAuthorization::where('viticulturist_id', $user->id)
            ->active()
            ->expiringSoon(60)
            ->count();

        return view('livewire.viticulturist.commercial-authorizations.index', [
            'entries'      => $entries,
            'exploitations'=> $exploitations,
            'authTypes'    => CommercialAuthorization::AUTHORIZATION_TYPES,
            'expiring'     => $expiring,
        ]);
    }
}
