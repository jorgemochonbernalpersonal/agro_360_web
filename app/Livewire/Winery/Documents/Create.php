<?php

namespace App\Livewire\Winery\Documents;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\WineryDocument;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $title             = '';
    public string $document_type     = 'other';
    public string $reference_number  = '';
    public string $issue_date        = '';
    public string $expiry_date       = '';
    public string $issuing_authority = '';
    public string $notes             = '';

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

        WineryDocument::create([
            'user_id'           => Auth::id(),
            'title'             => $this->title,
            'document_type'     => $this->document_type,
            'reference_number'  => $this->reference_number ?: null,
            'issue_date'        => $this->issue_date ?: null,
            'expiry_date'       => $this->expiry_date ?: null,
            'issuing_authority' => $this->issuing_authority ?: null,
            'notes'             => $this->notes ?: null,
            'active'            => true,
        ]);

        $this->toastSuccess("Documento «{$this->title}» creado correctamente.");
        $this->redirect(roleRoute('documents.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.documents.create', [
            'types' => WineryDocument::DOCUMENT_TYPES,
        ])->layout('layouts.app');
    }
}
