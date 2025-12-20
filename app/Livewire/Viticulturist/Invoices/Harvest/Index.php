<?php

namespace App\Livewire\Viticulturist\Invoices\Harvest;

use App\Models\Harvest;
use App\Models\Client;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCampaign = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCampaign' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedCampaign()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = Harvest::whereHas('activity', function($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
        ->with(['activity.plot', 'plotPlanting.grapeVariety', 'container'])
        ->whereDoesntHave('invoiceItems'); // Solo cosechas sin facturar

        if ($this->selectedCampaign) {
            $query->whereHas('activity', function($q) {
                $q->where('campaign_id', $this->selectedCampaign);
            });
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('activity.plot', function($subQ) {
                    $subQ->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('plotPlanting.grapeVariety', function($subQ) {
                    $subQ->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }

        $harvests = $query->orderBy('harvest_start_date', 'desc')
            ->paginate(15);

        $availableClients = Client::forUser($user->id)->active()->get();

        return view('livewire.viticulturist.invoices.harvest.index', [
            'harvests' => $harvests,
            'availableClients' => $availableClients,
        ])->layout('layouts.app');
    }
}
