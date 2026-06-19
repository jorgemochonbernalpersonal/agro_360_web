<?php

namespace App\Livewire\Winery\Documents;

use App\Livewire\Winery\AbstractCreate;
use App\Models\WineryDocument;

class Create extends AbstractCreate
{
    public string $title = '';

    public string $document_type = 'other';

    public string $reference_number = '';

    public string $issue_date = '';

    public string $expiry_date = '';

    public string $issuing_authority = '';

    public string $notes = '';

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'document_type' => ['required', 'in:'.implode(',', array_keys(WineryDocument::DOCUMENT_TYPES))],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'issuing_authority' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function performCreate(): void
    {
        WineryDocument::create([
            'user_id' => $this->ownerId(),
            'title' => $this->title,
            'document_type' => $this->document_type,
            'reference_number' => $this->reference_number ?: null,
            'issue_date' => $this->issue_date ?: null,
            'expiry_date' => $this->expiry_date ?: null,
            'issuing_authority' => $this->issuing_authority ?: null,
            'notes' => $this->notes ?: null,
            'active' => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Documento «:title» creado correctamente.', ['title' => $this->title]);
    }

    protected function indexRoute(): string
    {
        return 'winery.documents.index';
    }

    protected function viewData(): array
    {
        return [
            'types' => WineryDocument::documentTypeOptions(),
        ];
    }
}
