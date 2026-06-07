<?php

namespace App\Livewire\Viticulturist\Subcontracting;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\Subcontracting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithOwnershipRules, WithRoleAwareRedirect, WithToastNotifications;

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

    public function mount(): void
    {
        $this->service_date = now()->format('Y-m-d');

        $campaign = Campaign::where('viticulturist_id', Auth::id())
            ->where('active', true)->first();
        if ($campaign) {
            $this->campaign_id = (string) $campaign->id;
        }
    }

    public function save(): mixed
    {
        $this->validate();

        Subcontracting::create([
            'viticulturist_id' => Auth::id(),
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

        $this->toastSuccess(__('Subcontratación registrada correctamente.'));

        return $this->viticulturistRoleRedirect('subcontracting.index');
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.viticulturist.subcontracting.create', [
            'plots' => Plot::where('viticulturist_id', $user->id)->orderBy('name')->get(),
            'campaigns' => Campaign::where('viticulturist_id', $user->id)->orderByDesc('year')->get(),
            'serviceTypes' => Subcontracting::serviceTypeOptions(),
        ])->layout('layouts.app');
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
