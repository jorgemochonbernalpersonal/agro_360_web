<?php

namespace App\Livewire\Producer\Harvest;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Flujo "Promover a recepción de bodega" exclusivo del producer.
 *
 * Toma una cosecha del cuaderno de campo (activity_id set, winery_id null)
 * y crea la recepción de bodega correspondiente (winery_id set),
 * enlazando ambos registros via notebook_harvest_id.
 *
 * El producer solo necesita elegir el contenedor destino —
 * todos los demás datos se pre-rellenan desde el cuaderno.
 */
class PromoteToReception extends Component
{
    use WithToastNotifications, WithRoleAwareRedirect;

    public Harvest $notebookHarvest;

    // Pre-rellenado desde el cuaderno (editable)
    public string $harvest_start_date    = '';
    public string $total_weight          = '';
    public string $harvest_ticket_number = '';
    public string $price_per_kg          = '';
    public int    $vintage_year          = 0;

    // Calidad (pre-rellenado)
    public string $baume_degree      = '';
    public string $brix_degree       = '';
    public string $acidity_level     = '';
    public string $ph_level          = '';
    public string $potential_alcohol = '';
    public string $health_status     = '';

    // Estado sanitario (pre-rellenado)
    public string $sanitary_state_grapes    = '';
    public string $sanitary_state_agraces   = '';
    public string $sanitary_state_botrytis  = '';
    public string $sanitary_state_oidium    = '';
    public string $sanitary_state_mildew    = '';

    // Trazabilidad (pre-rellenado)
    public string $transport_document_number = '';
    public string $destination_rega_code     = '';
    public string $vehicle_plate             = '';

    // Único campo que el producer introduce
    public string $container_id = '';

    public string $notes = '';

    public function mount(Harvest $notebookHarvest): void
    {
        $user = Auth::user();

        abort_unless($user->isProducer(), 403);
        abort_unless($notebookHarvest->isViticulturistRecord(), 404);
        abort_unless(
            $notebookHarvest->activity?->viticulturist_id === $user->id,
            403
        );

        // Evitar doble-promoción
        if ($notebookHarvest->grapeReception()->exists()) {
            $this->toastError(__('Esta cosecha ya fue registrada como recepción de bodega.'));
            $this->redirect(
                route('producer.grape-reception.show', $notebookHarvest->grapeReception->id),
                navigate: true
            );
            return;
        }

        $this->notebookHarvest = $notebookHarvest->load([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'activity.campaign',
        ]);

        // Pre-rellenar desde cuaderno
        $this->harvest_start_date    = $notebookHarvest->harvest_start_date?->format('Y-m-d') ?? '';
        $this->total_weight          = (string) ($notebookHarvest->total_weight ?? '');
        $this->harvest_ticket_number = $notebookHarvest->harvest_ticket_number ?? '';
        $this->price_per_kg          = (string) ($notebookHarvest->price_per_kg ?? '');
        $this->vintage_year          = $notebookHarvest->vintage
            ?? ($notebookHarvest->harvest_start_date?->year ?? now()->year);

        $this->baume_degree          = (string) ($notebookHarvest->baume_degree ?? '');
        $this->brix_degree           = (string) ($notebookHarvest->brix_degree ?? '');
        $this->acidity_level         = (string) ($notebookHarvest->acidity_level ?? '');
        $this->ph_level              = (string) ($notebookHarvest->ph_level ?? '');
        $this->potential_alcohol     = (string) ($notebookHarvest->potential_alcohol ?? '');
        $this->health_status         = $notebookHarvest->health_status ?? '';

        $this->sanitary_state_grapes    = (string) ($notebookHarvest->sanitary_state_grapes ?? '');
        $this->sanitary_state_agraces   = (string) ($notebookHarvest->sanitary_state_agraces ?? '');
        $this->sanitary_state_botrytis  = (string) ($notebookHarvest->sanitary_state_botrytis ?? '');
        $this->sanitary_state_oidium    = (string) ($notebookHarvest->sanitary_state_oidium ?? '');
        $this->sanitary_state_mildew    = (string) ($notebookHarvest->sanitary_state_mildew ?? '');

        $this->transport_document_number = $notebookHarvest->transport_document_number ?? '';
        $this->destination_rega_code     = $notebookHarvest->destination_rega_code ?? '';
        $this->vehicle_plate             = $notebookHarvest->vehicle_plate ?? '';
        $this->notes                     = $notebookHarvest->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'container_id'        => ['required', Rule::exists('containers', 'id')->where('user_id', Auth::id())->where('unit', 'kg')],
            'harvest_start_date'  => ['required', 'date'],
            'total_weight'        => ['required', 'numeric', 'min:0.01'],
            'harvest_ticket_number' => ['nullable', 'string', 'max:50'],
            'price_per_kg'        => ['nullable', 'numeric', 'min:0'],
            'baume_degree'        => ['nullable', 'numeric', 'min:0', 'max:20'],
            'brix_degree'         => ['nullable', 'numeric', 'min:0', 'max:40'],
            'acidity_level'       => ['nullable', 'numeric', 'min:0', 'max:20'],
            'ph_level'            => ['nullable', 'numeric', 'min:0', 'max:14'],
            'potential_alcohol'   => ['nullable', 'numeric', 'min:0', 'max:25'],
            'health_status'       => ['nullable', 'in:sano,daño_leve,daño_moderado,daño_grave'],
            'notes'               => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'container_id.required'      => __('Selecciona el depósito de destino.'),
            'harvest_start_date.required' => __('La fecha de recepción es obligatoria.'),
            'total_weight.required'       => __('El peso es obligatorio.'),
            'total_weight.min'            => __('El peso debe ser mayor que 0.'),
        ];
    }

    public function save(): mixed
    {
        $this->validate();

        $wineryId  = Auth::id();
        $weight    = (float) $this->total_weight;
        $container = Container::where('user_id', $wineryId)->findOrFail((int) $this->container_id);

        // Capacidad disponible
        if (! $container->hasAvailableCapacity($weight)) {
            $available = number_format($container->getAvailableCapacity(), 0);
            $this->addError('container_id', __('El contenedor «:name» no tiene capacidad suficiente. Disponible: :available kg.', ['name' => $container->name, 'available' => $available]));
            return null;
        }

        $planting   = $this->notebookHarvest->plotPlanting;
        $campaign   = Campaign::getOrCreateActiveForYear($wineryId, $this->vintage_year);
        abort_if(! $campaign, 500, 'No se pudo obtener la campaña.');

        // Guard: batch cerrado
        $existingBatch = GrapeReceptionBatch::where('winery_id', $wineryId)
            ->where('plot_planting_id', $planting->id)
            ->where('campaign_id', $campaign->id)
            ->first();

        if ($existingBatch && $existingBatch->status === 'closed') {
            $this->toastError(__('El lote de esta plantación está cerrado. Réabrelo desde el Cuadro de Mando.'));
            return null;
        }

        $pricePerKg = $this->price_per_kg ? (float) $this->price_per_kg : null;
        $totalValue = ($weight && $pricePerKg) ? round($weight * $pricePerKg, 3) : null;

        try {
            $reception = DB::transaction(function () use (
                $wineryId, $weight, $pricePerKg, $totalValue, $container, $planting, $campaign
            ) {
                $batch = GrapeReceptionBatch::firstOrCreate(
                    [
                        'winery_id'        => $wineryId,
                        'plot_planting_id' => $planting->id,
                        'campaign_id'      => $campaign->id,
                    ],
                    [
                        'viticulturist_id' => $wineryId, // producer es su propio viticultor
                        'vintage_year'     => $this->vintage_year,
                        'total_weight_kg'  => 0,
                        'status'           => 'open',
                    ]
                );

                $reception = Harvest::create([
                    'notebook_harvest_id'        => $this->notebookHarvest->id,
                    'winery_id'                  => $wineryId,
                    'batch_id'                   => $batch->id,
                    'plot_planting_id'            => $planting->id,
                    'vintage'                    => $this->vintage_year,
                    'harvest_start_date'         => $this->harvest_start_date,
                    'total_weight'               => $weight,
                    'container_id'               => $container->id,
                    'price_per_kg'               => $pricePerKg,
                    'total_value'                => $totalValue,
                    'harvest_ticket_number'      => $this->harvest_ticket_number ?: null,
                    'baume_degree'               => $this->baume_degree ?: null,
                    'brix_degree'                => $this->brix_degree ?: null,
                    'acidity_level'              => $this->acidity_level ?: null,
                    'ph_level'                   => $this->ph_level ?: null,
                    'potential_alcohol'          => $this->potential_alcohol ?: null,
                    'health_status'              => $this->health_status ?: null,
                    'sanitary_state_grapes'      => $this->sanitary_state_grapes ?: null,
                    'sanitary_state_agraces'     => $this->sanitary_state_agraces ?: null,
                    'sanitary_state_botrytis'    => $this->sanitary_state_botrytis ?: null,
                    'sanitary_state_oidium'      => $this->sanitary_state_oidium ?: null,
                    'sanitary_state_mildew'      => $this->sanitary_state_mildew ?: null,
                    'transport_document_number'  => $this->transport_document_number ?: null,
                    'destination_rega_code'      => $this->destination_rega_code ?: null,
                    'vehicle_plate'              => $this->vehicle_plate ?: null,
                    'notes'                      => $this->notes ?: null,
                    'status'                     => 'active',
                ]);

                $batch->recalculateTotal();

                return $reception;
            });

            $this->toastSuccess(__('Cosecha registrada en bodega correctamente.'));
            return $this->roleRedirect('grape-reception.show', $reception);

        } catch (\Exception $e) {
            \Log::error('Error al promover cosecha a recepción', [
                'notebook_harvest_id' => $this->notebookHarvest->id,
                'error'               => $e->getMessage(),
            ]);
            $this->toastError(__('Error al registrar la recepción: :error', ['error' => $e->getMessage()]));
        }

        return null;
    }

    public function render()
    {
        $availableContainers = Container::where('user_id', Auth::id())
            ->where('archived', false)
            ->where('unit', 'kg')
            ->orderBy('name')
            ->get(['id', 'name', 'capacity', 'used_capacity']);

        return view('livewire.producer.harvest.promote-to-reception', [
            'availableContainers' => $availableContainers,
        ])->layout('layouts.app');
    }
}
