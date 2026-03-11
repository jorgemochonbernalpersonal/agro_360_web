<?php

namespace App\Livewire\Viticulturist\Vendimia;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\HarvestDelivery;
use App\Models\Plot;
use App\Models\PlotPlanting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateDelivery extends Component
{
    use WithToastNotifications;

    public string $plot_id          = '';
    public string $plot_planting_id = '';
    public string $vintage_year     = '';
    public string $buyer_name       = '';
    public string $delivered_kg     = '';
    public string $price_per_kg     = '';
    public string $delivery_date    = '';
    public string $ticket_number          = '';
    public string $destination_rega_code = '';
    public string $vehicle_plate         = '';
    public string $notes                 = '';
    public string $harvest_time          = '';
    public bool   $disqualified          = false;
    public string $disqualified_reason   = '';
    public string $baume_degree          = '';
    public string $brix_degree           = '';
    public string $potential_alcohol     = '';
    public string $acidity_level         = '';
    public string $ph_level              = '';

    public function mount(Request $request): void
    {
        $this->vintage_year  = (string) now()->year;
        $this->delivery_date = now()->format('Y-m-d');

        if ($year = (int) $request->query('vintage')) {
            if ($year >= 2000 && $year <= 2100) {
                $this->vintage_year = (string) $year;
            }
        }

        if ($plantingId = $request->query('planting')) {
            $ownPlotIds = Plot::forUser(Auth::user())->pluck('id');
            $planting   = PlotPlanting::whereIn('plot_id', $ownPlotIds)->find($plantingId);
            if ($planting && $planting->active) {
                $this->plot_planting_id = (string) $planting->id;
                $this->plot_id          = (string) $planting->plot_id;
            }
        }
    }

    public function updatedPlotId(): void
    {
        $this->plot_planting_id = '';
    }

    public function updatedDeliveredKg(string $value): void
    {
        // reactive total price is computed in view via Alpine / Blade — no server action needed
    }

    protected function rules(): array
    {
        return [
            'plot_planting_id' => [
                'nullable',
                Rule::exists('plot_plantings', 'id')->whereIn(
                    'plot_id',
                    Plot::forUser(Auth::user())->pluck('id')
                ),
            ],
            'vintage_year'     => 'required|integer|min:2000|max:2100',
            'buyer_name'       => 'required|string|max:255',
            'delivered_kg'     => 'required|numeric|min:0.01',
            'price_per_kg'     => 'nullable|numeric|min:0',
            'delivery_date'    => 'required|date',
            'ticket_number'          => 'nullable|string|max:100',
            'destination_rega_code'  => 'nullable|string|max:20',
            'vehicle_plate'          => 'nullable|string|max:20',
            'notes'                  => 'nullable|string',
            'harvest_time'           => ['nullable', 'date_format:H:i'],
            'disqualified'           => ['boolean'],
            'disqualified_reason'    => ['nullable', 'string', 'max:500'],
            'baume_degree'           => ['nullable', 'numeric', 'min:0', 'max:20'],
            'brix_degree'            => ['nullable', 'numeric', 'min:0', 'max:40'],
            'potential_alcohol'      => ['nullable', 'numeric', 'min:0', 'max:25'],
            'acidity_level'          => ['nullable', 'numeric', 'min:0', 'max:20'],
            'ph_level'               => ['nullable', 'numeric', 'min:0', 'max:14'],
        ];
    }

    public function save(): mixed
    {
        $this->validate();

        $totalPrice = null;
        if ($this->price_per_kg && $this->delivered_kg) {
            $totalPrice = round((float) $this->price_per_kg * (float) $this->delivered_kg, 2);
        }

        HarvestDelivery::create([
            'viticulturist_id' => Auth::id(),
            'plot_planting_id' => $this->plot_planting_id ?: null,
            'vintage_year'     => (int) $this->vintage_year,
            'buyer_name'       => $this->buyer_name,
            'delivered_kg'     => $this->delivered_kg,
            'price_per_kg'     => $this->price_per_kg ?: null,
            'total_price'      => $totalPrice,
            'delivery_date'    => $this->delivery_date,
            'ticket_number'         => $this->ticket_number ?: null,
            'destination_rega_code' => $this->destination_rega_code ?: null,
            'vehicle_plate'         => $this->vehicle_plate ?: null,
            'notes'                 => $this->notes ?: null,
            'harvest_time'          => $this->harvest_time ?: null,
            'disqualified'          => $this->disqualified,
            'disqualified_reason'   => ($this->disqualified && $this->disqualified_reason) ? $this->disqualified_reason : null,
            'baume_degree'          => $this->baume_degree ?: null,
            'brix_degree'           => $this->brix_degree ?: null,
            'potential_alcohol'     => $this->potential_alcohol ?: null,
            'acidity_level'         => $this->acidity_level ?: null,
            'ph_level'              => $this->ph_level ?: null,
        ]);

        $this->toastSuccess('Entrega registrada correctamente.');

        return redirect()->route('viticulturist.vendimia.index');
    }

    public function render()
    {
        $user = Auth::user();

        $plots = Plot::forUser($user)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $availablePlantings = collect();
        if ($this->plot_id) {
            $availablePlantings = PlotPlanting::where('plot_id', $this->plot_id)
                ->where('active', true)
                ->with('grapeVariety')
                ->orderBy('id')
                ->get();
        }

        return view('livewire.viticulturist.vendimia.create-delivery', [
            'plots'              => $plots,
            'availablePlantings' => $availablePlantings,
        ])->layout('layouts.app', ['title' => 'Registrar entrega - Agro365']);
    }
}
