<?php

namespace App\Livewire\Winery\Documents;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\WineryDocument;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public WineryDocument $wineryDocument;

    public string $title             = '';
    public string $document_type     = '';
    public string $reference_number  = '';
    public string $issue_date        = '';
    public string $expiry_date       = '';
    public string $issuing_authority = '';
    public string $notes             = '';

    public function mount(WineryDocument $wineryDocument): void
    {
        abort_if($wineryDocument->user_id !== Auth::id(), 403);
        $this->wineryDocument    = $wineryDocument;
        $this->title             = $wineryDocument->title;
        $this->document_type     = $wineryDocument->document_type;
        $this->reference_number  = $wineryDocument->reference_number ?? '';
        $this->issue_date        = $wineryDocument->issue_date?->toDateString() ?? '';
        $this->expiry_date       = $wineryDocument->expiry_date?->toDateString() ?? '';
        $this->issuing_authority = $wineryDocument->issuing_authority ?? '';
        $this->notes             = $wineryDocument->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:200'],
            'document_type'     => ['required', 'in:' . implode(',', array_keys(WineryDocument::DOCUMENT_TYPES))],
            'reference_number'  => ['nullable', 'string', 'max:100'],
            'issue_date'        => ['nullable', 'date'],
            'expiry_date'       => ['nullable', 'date'],
            'issuing_authority' => ['nullable', 'string', 'max:200'],
            'notes'             => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->wineryDocument->update([
            'title'             => $this->title,
            'document_type'     => $this->document_type,
            'reference_number'  => $this->reference_number ?: null,
            'issue_date'        => $this->issue_date ?: null,
            'expiry_date'       => $this->expiry_date ?: null,
            'issuing_authority' => $this->issuing_authority ?: null,
            'notes'             => $this->notes ?: null,
        ]);

        $this->toastSuccess('Documento actualizado correctamente.');
        $this->redirect(route('winery.documents.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.documents.edit', [
            'types' => WineryDocument::DOCUMENT_TYPES,
        ])->layout('layouts.app');
    }
}
