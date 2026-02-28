<?php

namespace App\Livewire\Viticulturist\MarketedHarvests;

use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\MarketedHarvest;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithToastNotifications;

    // Filters
    public $filterCampaign = '';
    public $filterDestination = '';

    // Form
    public bool $showModal = false;
    public ?int $editingId = null;

    public $harvest_id = '';
    public $delivery_date = '';
    public $quantity_kg = '';
    public $destination_type = '';
    public $buyer_name = '';
    public $buyer_rega_code = '';
    public $transport_document = '';
    public $vehicle_plate = '';
    public $price_per_kg = '';
    public $total_value = '';
    public $notes = '';

    public function mount()
    {
        $user = Auth::user();
        $campaign = Campaign::getOrCreateActiveForYear($user->id);
        $this->filterCampaign = $campaign?->id ?? '';
        $this->delivery_date = now()->format('Y-m-d');
    }

    public function updatedQuantityKg($value)
    {
        if ($value && $this->price_per_kg) {
            $this->total_value = round($value * $this->price_per_kg, 2);
        }
    }

    public function updatedPricePerKg($value)
    {
        if ($value && $this->quantity_kg) {
            $this->total_value = round($this->quantity_kg * $value, 2);
        }
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $entry = MarketedHarvest::where('viticulturist_id', Auth::id())->findOrFail($id);
        $this->editingId = $id;
        $this->harvest_id = $entry->harvest_id;
        $this->delivery_date = $entry->delivery_date->format('Y-m-d');
        $this->quantity_kg = $entry->quantity_kg;
        $this->destination_type = $entry->destination_type;
        $this->buyer_name = $entry->buyer_name ?? '';
        $this->buyer_rega_code = $entry->buyer_rega_code ?? '';
        $this->transport_document = $entry->transport_document ?? '';
        $this->vehicle_plate = $entry->vehicle_plate ?? '';
        $this->price_per_kg = $entry->price_per_kg ?? '';
        $this->total_value = $entry->total_value ?? '';
        $this->notes = $entry->notes ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate($this->rules());

        $user = Auth::user();

        $harvest = Harvest::findOrFail($this->harvest_id);
        $campaignId = $harvest->activity->campaign_id;

        $data = [
            'harvest_id'        => $this->harvest_id,
            'campaign_id'       => $campaignId,
            'viticulturist_id'  => $user->id,
            'delivery_date'     => $this->delivery_date,
            'quantity_kg'       => $this->quantity_kg,
            'destination_type'  => $this->destination_type,
            'buyer_name'        => $this->buyer_name ?: null,
            'buyer_rega_code'   => $this->buyer_rega_code ?: null,
            'transport_document'=> $this->transport_document ?: null,
            'vehicle_plate'     => $this->vehicle_plate ?: null,
            'price_per_kg'      => $this->price_per_kg ?: null,
            'total_value'       => $this->total_value ?: null,
            'notes'             => $this->notes ?: null,
        ];

        if ($this->editingId) {
            MarketedHarvest::where('viticulturist_id', $user->id)
                ->findOrFail($this->editingId)
                ->update($data);
            $this->toastSuccess('Entrega actualizada correctamente.');
        } else {
            MarketedHarvest::create($data);
            $this->toastSuccess('Entrega registrada correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function generateInvoice(int $id): void
    {
        $entry = MarketedHarvest::where('viticulturist_id', Auth::id())->findOrFail($id);

        $this->redirect(
            route('viticulturist.invoices.create') .
            '?harvest_id=' . $entry->harvest_id .
            '&marketed_harvest_id=' . $entry->id
        );
    }

    public function delete(int $id)
    {
        MarketedHarvest::where('viticulturist_id', Auth::id())->findOrFail($id)->delete();
        $this->toastSuccess('Entrega eliminada.');
    }

    protected function rules(): array
    {
        return [
            'harvest_id'      => 'required|exists:harvests,id',
            'delivery_date'   => 'required|date',
            'quantity_kg'     => 'required|numeric|min:0.01',
            'destination_type'=> 'required|in:own_winery,cooperative,third_party,other',
            'buyer_name'      => 'nullable|string|max:255',
            'buyer_rega_code' => 'nullable|string|max:20',
            'transport_document' => 'nullable|string|max:100',
            'vehicle_plate'   => 'nullable|string|max:20',
            'price_per_kg'    => 'nullable|numeric|min:0',
            'total_value'     => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
        ];
    }

    protected function resetForm()
    {
        $this->editingId = null;
        $this->harvest_id = '';
        $this->delivery_date = now()->format('Y-m-d');
        $this->quantity_kg = '';
        $this->destination_type = '';
        $this->buyer_name = '';
        $this->buyer_rega_code = '';
        $this->transport_document = '';
        $this->vehicle_plate = '';
        $this->price_per_kg = '';
        $this->total_value = '';
        $this->notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();

        $query = MarketedHarvest::with(['harvest.plotPlanting.plot', 'harvest.plotPlanting.grape', 'invoice'])
            ->where('viticulturist_id', $user->id)
            ->active();

        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterDestination) {
            $query->where('destination_type', $this->filterDestination);
        }

        $entries = $query->orderByDesc('delivery_date')->paginate(15);

        $campaigns = Campaign::forViticulturist($user->id)->orderByDesc('year')->get();
        $harvests  = Harvest::whereHas('activity', fn($q) => $q->where('viticulturist_id', $user->id))
            ->with(['plotPlanting.plot', 'plotPlanting.grape'])
            ->orderByDesc('harvest_start_date')
            ->get();

        return view('livewire.viticulturist.marketed-harvests.index', [
            'entries'      => $entries,
            'campaigns'    => $campaigns,
            'harvests'     => $harvests,
            'destinations' => MarketedHarvest::DESTINATION_TYPES,
        ]);
    }
}
