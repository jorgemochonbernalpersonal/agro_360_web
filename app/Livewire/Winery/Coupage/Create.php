<?php

namespace App\Livewire\Winery\Coupage;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\Oenologist;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineTransfer;
use App\Services\WineContainerStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Create extends Component
{
    use WithOwnershipRules, WithToastNotifications;

    public string $target_wine_id       = '';
    public string $to_container_id      = '';
    public string $unit_of_measurement_id = '';
    public string $coupage_date         = '';
    public string $oenologist_id        = '';
    public string $notes                = '';

    public array $sources = [
        ['wine_id' => '', 'from_container_id' => '', 'quantity' => ''],
    ];

    public function mount(): void
    {
        $this->coupage_date = now()->format('Y-m-d');
    }

    public function addSource(): void
    {
        $this->sources[] = ['wine_id' => '', 'from_container_id' => '', 'quantity' => ''];
    }

    public function removeSource(int $index): void
    {
        if (count($this->sources) <= 1) return;
        unset($this->sources[$index]);
        $this->sources = array_values($this->sources);
    }

    protected function rules(): array
    {
        return [
            'target_wine_id'          => ['required', Rule::exists('wines', 'id')->where('user_id', Auth::id())],
            'to_container_id'         => ['required', Rule::exists('containers', 'id')->where('user_id', Auth::id())],
            'unit_of_measurement_id'  => ['required', 'exists:units_of_measurement,id'],
            'coupage_date'            => ['required', 'date'],
            'oenologist_id'           => $this->ownedOenologistRule(),
            'notes'                   => ['nullable', 'string', 'max:2000'],
            'sources'                 => ['required', 'array', 'min:1'],
            'sources.*.wine_id'       => ['required', Rule::exists('wines', 'id')->where('user_id', Auth::id())],
            'sources.*.from_container_id' => ['nullable', Rule::exists('containers', 'id')->where('user_id', Auth::id())],
            'sources.*.quantity'      => ['required', 'numeric', 'min:0.001'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $userId    = Auth::id();
        $destId    = $this->to_container_id;
        $dest      = Container::where('user_id', $userId)->findOrFail($destId);
        $service   = app(WineContainerStockService::class);

        $totalQty = collect($this->sources)->sum('quantity');

        if ($dest->getAvailableCapacity() < $totalQty) {
            $this->addError('to_container_id', 'El contenedor destino no tiene capacidad suficiente (' . number_format($dest->getAvailableCapacity(), 1) . ' L disponibles para ' . number_format($totalQty, 1) . ' L solicitados).');
            return;
        }

        DB::transaction(function () use ($userId, $service) {
            foreach ($this->sources as $source) {
                $transfer = WineTransfer::create([
                    'wine_id'                => $this->target_wine_id,
                    'source_wine_id'         => $source['wine_id'],
                    'from_container_id'      => $source['from_container_id'] ?: null,
                    'to_container_id'        => $this->to_container_id,
                    'quantity'               => $source['quantity'],
                    'unit_of_measurement_id' => $this->unit_of_measurement_id,
                    'transfer_type'          => 'blending',
                    'transfer_date'          => $this->coupage_date,
                    'oenologist_id'          => $this->oenologist_id ?: null,
                    'notes'                  => $this->notes ?: null,
                    'created_by'             => $userId,
                ]);

                $service->recordTransfer($transfer);
            }
        });

        $this->toastSuccess('Coupage registrado correctamente.');
        $this->redirect(roleRoute('coupage.index'), navigate: true);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $userId = Auth::id();

        return view('livewire.winery.coupage.create', [
            'wines'      => Wine::where('user_id', $userId)->whereNotIn('status', ['cancelled'])->orderByDesc('vintage')->orderBy('name')->get(['id', 'name', 'vintage', 'wine_type']),
            'containers' => Container::where('user_id', $userId)->where('archived', false)->orderBy('name')->get(['id', 'name', 'type']),
            'units'      => UnitOfMeasurement::where('category', 'volume')->orderBy('name')->get(['id', 'name', 'symbol']),
            'oenologists'=> Oenologist::where('user_id', $userId)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
