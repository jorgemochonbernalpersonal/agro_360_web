<?php

namespace App\Livewire\Viticulturist\Subcontracting;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\Subcontracting;
use Illuminate\Support\Facades\Auth;

class Edit extends AbstractEdit
{
    public Subcontracting $record;

    public string $plot_id = '';

    public string $campaign_id = '';

    public string $service_type = 'other';

    public string $company_name = '';

    public string $contact_person = '';

    public string $contact_phone = '';

    public string $service_date = '';

    public string $service_end_date = '';

    public string $amount = '';

    public bool $invoiced = false;

    public string $invoice_number = '';

    public string $description = '';

    public string $notes = '';

    public function mount(Subcontracting $record): void
    {
        $this->authorizeOwnership($record);

        $this->record = $record;
        $this->plot_id = (string) ($record->plot_id ?? '');
        $this->campaign_id = (string) ($record->campaign_id ?? '');
        $this->service_type = $record->service_type;
        $this->company_name = $record->company_name;
        $this->contact_person = $record->contact_person ?? '';
        $this->contact_phone = $record->contact_phone ?? '';
        $this->service_date = $record->service_date->format('Y-m-d');
        $this->service_end_date = $record->service_end_date?->format('Y-m-d') ?? '';
        $this->amount = (string) ($record->amount ?? '');
        $this->invoiced = $record->invoiced;
        $this->invoice_number = $record->invoice_number ?? '';
        $this->description = $record->description ?? '';
        $this->notes = $record->notes ?? '';
    }

    protected function performUpdate(): void
    {
        $this->record->update([
            'plot_id' => $this->plot_id ?: null,
            'campaign_id' => $this->campaign_id ?: null,
            'service_type' => $this->service_type,
            'company_name' => $this->company_name,
            'contact_person' => $this->contact_person ?: null,
            'contact_phone' => $this->contact_phone ?: null,
            'service_date' => $this->service_date,
            'service_end_date' => $this->service_end_date ?: null,
            'amount' => $this->amount ?: null,
            'invoiced' => $this->invoiced,
            'invoice_number' => $this->invoice_number ?: null,
            'description' => $this->description ?: null,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Subcontratación actualizada correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'subcontracting.index';
    }

    protected function viewData(): array
    {
        $userId = Auth::id();

        return [
            'plots' => Plot::where('viticulturist_id', $userId)->orderBy('name')->get(),
            'campaigns' => Campaign::where('viticulturist_id', $userId)->orderByDesc('year')->get(),
            'serviceTypes' => Subcontracting::serviceTypeOptions(),
        ];
    }

    protected function rules(): array
    {
        return [
            'plot_id' => $this->plotOwnershipRule(false),
            'campaign_id' => $this->campaignOwnershipRule(false),
            'service_type' => 'required|in:'.implode(',', array_keys(Subcontracting::SERVICE_TYPES)),
            'company_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'service_date' => 'required|date',
            'service_end_date' => 'nullable|date|after_or_equal:service_date',
            'amount' => 'nullable|numeric|min:0',
            'invoiced' => 'boolean',
            'invoice_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}
