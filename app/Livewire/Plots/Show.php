<?php

namespace App\Livewire\Plots;

use App\Livewire\Concerns\GeneratesCatastroGeometry;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotEnvironment;
use App\Models\SigpacCode;
use App\Services\SigpacGeometryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Show extends Component
{
    use WithToastNotifications, GeneratesCatastroGeometry;

    public Plot $plot;

    // Tab
    public string $currentTab = 'info';
    public bool   $fromVisual  = false;

    // Entorno de parcela (por campaña activa)
    public ?int $env_id = null;
    public string $env_campaign_id = '';
    public bool $env_water_intake_nearby = false;
    public string $env_water_intake_distance_m = '';
    public bool $env_protected_zone_total = false;
    public bool $env_protected_zone_partial = false;
    public string $env_protection_zone_type = '';
    public string $env_buffer_zone_m = '';
    public string $env_slope_pct = '';
    public bool $env_erosion_risk = false;
    public string $env_notes = '';

    public function mount(Plot $plot)
    {
        if (!Auth::user()->can('view', $plot)) {
            abort(403);
        }

        $this->fromVisual = request()->query('from') === 'visual';

        $this->plot = $plot->load([
            'viticulturist',
            'autonomousCommunity',
            'province',
            'municipality',
            'sigpacUses',
            'sigpacCodes',
            'multiplePlotSigpacs.sigpacCode',
            'multiplePlotSigpacs.plotGeometry',
            'plantings.grapeVariety',
        ]);

        if (Auth::user()->hasViticulturistAccess()) {
            $this->loadEnvironment();
        }
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
    }

    private function loadEnvironment(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear(Auth::id());
        if (!$campaign) {
            return;
        }

        $this->env_campaign_id = $campaign->id;

        $env = PlotEnvironment::where('viticulturist_id', Auth::id())
            ->where('plot_id', $this->plot->id)
            ->where('campaign_id', $campaign->id)
            ->first();

        if ($env) {
            $this->env_id                      = $env->id;
            $this->env_water_intake_nearby      = (bool) $env->water_intake_nearby;
            $this->env_water_intake_distance_m  = $env->water_intake_distance_m ?? '';
            $this->env_protected_zone_total     = (bool) $env->protected_zone_total;
            $this->env_protected_zone_partial   = (bool) $env->protected_zone_partial;
            $this->env_protection_zone_type     = $env->protection_zone_type ?? '';
            $this->env_buffer_zone_m            = $env->buffer_zone_m ?? '';
            $this->env_slope_pct                = $env->slope_pct ?? '';
            $this->env_erosion_risk             = (bool) $env->erosion_risk;
            $this->env_notes                    = $env->notes ?? '';
        }
    }

    public function saveEnvironment(): void
    {
        if (!Auth::user()->hasViticulturistAccess()) {
            return;
        }

        $this->validate([
            'env_water_intake_distance_m' => 'nullable|numeric|min:0',
            'env_protection_zone_type'    => 'nullable|string|max:100',
            'env_buffer_zone_m'           => 'nullable|numeric|min:0',
            'env_slope_pct'               => 'nullable|numeric|min:0|max:100',
            'env_notes'                   => 'nullable|string',
        ]);

        $env = PlotEnvironment::updateOrCreate(
            [
                'campaign_id' => $this->env_campaign_id,
                'plot_id'     => $this->plot->id,
            ],
            [
                'viticulturist_id'        => Auth::id(),
                'water_intake_nearby'     => $this->env_water_intake_nearby,
                'water_intake_distance_m' => $this->env_water_intake_distance_m ?: null,
                'protected_zone_total'    => $this->env_protected_zone_total,
                'protected_zone_partial'  => $this->env_protected_zone_partial,
                'protection_zone_type'    => $this->env_protection_zone_type ?: null,
                'buffer_zone_m'           => $this->env_buffer_zone_m ?: null,
                'slope_pct'               => $this->env_slope_pct ?: null,
                'erosion_risk'            => $this->env_erosion_risk,
                'notes'                   => $this->env_notes ?: null,
            ]
        );

        $this->env_id = $env->id;
        $this->toastSuccess(__('Entorno de parcela guardado correctamente.'));
    }

    public function generateMap($plotId = null, $sigpacCodeId = null)
    {
        $plotId = $plotId ?? $this->plot->id;
        $plot = Plot::findOrFail($plotId);

        if (!Auth::user()->can('update', $plot)) {
            $this->toastError(__('No tienes permiso para modificar esta parcela.'));
            return;
        }

        if ($sigpacCodeId) {
            $sigpacCode = SigpacCode::findOrFail($sigpacCodeId);
            $sigpacCodes = collect([$sigpacCode]);
        } else {
            $sigpacCodes = $plot->sigpacCodes;
        }

        if ($sigpacCodes->isEmpty()) {
            $this->toastError(__('Esta parcela no tiene códigos SIGPAC asociados.'));
            return;
        }

        $service = app(SigpacGeometryService::class);
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            foreach ($sigpacCodes as $sigpacCode) {
                try {
                    $wkt = $service->fetchWkt($sigpacCode);

                    if (!$wkt) {
                        $errorCount++;
                        $errors[] = "No se pudieron obtener coordenadas para el código {$sigpacCode->code}";
                        continue;
                    }

                    if (!preg_match('/^(POLYGON|MULTIPOLYGON|LINESTRING|POINT)\s*\(.+\)$/i', $wkt)) {
                        $errorCount++;
                        $errors[] = "Formato de coordenadas inválido para el código {$sigpacCode->code}";
                        continue;
                    }

                    $service->upsertGeometry($plotId, $sigpacCode, $wkt);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Error procesando código {$sigpacCode->code}: " . $e->getMessage();
                    Log::error('Error generating map for sigpac code', [
                        'sigpac_code_id' => $sigpacCode->id,
                        'plot_id' => $plotId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            if ($successCount > 0) {
                $message = $successCount === 1
                    ? 'Mapa generado correctamente para 1 código SIGPAC.'
                    : "Mapas generados correctamente para {$successCount} códigos SIGPAC.";
                $this->toastSuccess($message);
                $this->plot->refresh();
                $this->plot->load([
                    'viticulturist',
                    'autonomousCommunity',
                    'province',
                    'municipality',
                    'sigpacUses',
                    'sigpacCodes',
                    'multiplePlotSigpacs.sigpacCode',
                    'multiplePlotSigpacs.plotGeometry',
                ]);
            }

            if ($errorCount > 0) {
                $errorMessage = "Error al generar {$errorCount} mapa(s). " . implode(' ', array_slice($errors, 0, 3));
                $this->toastError($errorMessage);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating maps from SIGPAC', [
                'plot_id' => $plotId,
                'error' => $e->getMessage(),
            ]);
            $this->toastError(__('Error al generar los mapas. Por favor, intenta de nuevo.'));
        }
    }

    public function render()
    {
        $isViticulturist = Auth::user()->hasViticulturistAccess();
        $activeCampaign = $isViticulturist
            ? Campaign::getOrCreateActiveForYear(Auth::id())
            : null;

        return view('livewire.plots.show', compact('isViticulturist', 'activeCampaign'))
            ->layout('layouts.app', [
                'title' => $this->plot->name . ' - Parcela - Agro365',
                'description' => __('Detalles de la parcela ') . $this->plot->name . '. Información completa, códigos SIGPAC, ubicación y plantaciones asociadas.',
            ]);
    }
}
