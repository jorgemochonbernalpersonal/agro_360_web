<?php

namespace App\Livewire\Winery\Documents;

use App\Livewire\Winery\AbstractEdit;
use App\Models\WineryDocument;

class Edit extends AbstractEdit
{
    public WineryDocument $wineryDocument;

    public string $title = '';

    public string $document_type = '';

    public string $reference_number = '';

    public string $issue_date = '';

    public string $expiry_date = '';

    public string $issuing_authority = '';

    public string $notes = '';

    public function mount(WineryDocument $wineryDocument): void
    {
        $this->authorize('update', $wineryDocument);
        $this->wineryDocument = $wineryDocument;
        $this->title = $wineryDocument->title;
        $this->document_type = $wineryDocument->document_type;
        $this->reference_number = $wineryDocument->reference_number ?? '';
        $this->issue_date = $wineryDocument->issue_date?->toDateString() ?? '';
        $this->expiry_date = $wineryDocument->expiry_date?->toDateString() ?? '';
        $this->issuing_authority = $wineryDocument->issuing_authority ?? '';
        $this->notes = $wineryDocument->notes ?? '';
    }

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

    protected function performUpdate(): void
    {
        $this->wineryDocument->update([
            'title' => $this->title,
            'document_type' => $this->document_type,
            'reference_number' => $this->reference_number ?: null,
            'issue_date' => $this->issue_date ?: null,
            'expiry_date' => $this->expiry_date ?: null,
            'issuing_authority' => $this->issuing_authority ?: null,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Documento actualizado correctamente.');
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
