<?php

namespace App\Livewire\Viticulturist\AdvisoryMemberships;

use App\Models\AdvisoryMembership;
use App\Models\Campaign;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithToastNotifications;

    // Filters
    public $filterSpecialty = '';

    // Form
    public bool $showModal = false;
    public ?int $editingId = null;

    public $campaign_id = '';
    public $advisor_name = '';
    public $license_number = '';
    public $specialty = 'phytosanitary';
    public $company_name = '';
    public $phone = '';
    public $email = '';

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $entry = AdvisoryMembership::where('viticulturist_id', Auth::id())->findOrFail($id);
        $this->editingId = $id;
        $this->campaign_id = $entry->campaign_id ?? '';
        $this->advisor_name = $entry->advisor_name;
        $this->license_number = $entry->license_number;
        $this->specialty = $entry->specialty;
        $this->company_name = $entry->company_name ?? '';
        $this->phone = $entry->phone ?? '';
        $this->email = $entry->email ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate($this->rules());
        $user = Auth::user();

        $data = [
            'viticulturist_id' => $user->id,
            'campaign_id'      => $this->campaign_id ?: null,
            'advisor_name'     => $this->advisor_name,
            'license_number'   => $this->license_number,
            'specialty'        => $this->specialty,
            'company_name'     => $this->company_name ?: null,
            'phone'            => $this->phone ?: null,
            'email'            => $this->email ?: null,
            'active'           => true,
        ];

        if ($this->editingId) {
            AdvisoryMembership::where('viticulturist_id', $user->id)
                ->findOrFail($this->editingId)
                ->update($data);
            $this->toastSuccess('Asesor actualizado correctamente.');
        } else {
            AdvisoryMembership::create($data);
            $this->toastSuccess('Asesor registrado correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function deactivate(int $id)
    {
        AdvisoryMembership::where('viticulturist_id', Auth::id())
            ->findOrFail($id)
            ->update(['active' => false]);
        $this->toastSuccess('Asesor desactivado.');
    }

    protected function rules(): array
    {
        return [
            'campaign_id'    => 'nullable|exists:campaigns,id',
            'advisor_name'   => 'required|string|max:255',
            'license_number' => 'required|string|max:50',
            'specialty'      => 'required|in:' . implode(',', array_keys(AdvisoryMembership::SPECIALTIES)),
            'company_name'   => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
        ];
    }

    protected function resetForm()
    {
        $this->editingId = null;
        $this->campaign_id = '';
        $this->advisor_name = '';
        $this->license_number = '';
        $this->specialty = 'phytosanitary';
        $this->company_name = '';
        $this->phone = '';
        $this->email = '';
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();

        $query = AdvisoryMembership::where('viticulturist_id', $user->id)->active();

        if ($this->filterSpecialty) {
            $query->where('specialty', $this->filterSpecialty);
        }

        $entries = $query->orderBy('advisor_name')->paginate(20);
        $campaigns = Campaign::forViticulturist($user->id)->orderByDesc('year')->get();

        return view('livewire.viticulturist.advisory-memberships.index', [
            'entries'     => $entries,
            'campaigns'   => $campaigns,
            'specialties' => AdvisoryMembership::SPECIALTIES,
        ]);
    }
}
