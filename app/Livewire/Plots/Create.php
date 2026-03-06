<?php

namespace App\Livewire\Plots;

use App\Livewire\Concerns\WithRoleBasedFields;
use App\Livewire\Concerns\WithUserFilters;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use App\Models\SigpacUse;
use App\Models\ViticulturistSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Create extends Component
{
    use WithRoleBasedFields, WithUserFilters, WithToastNotifications;

    public $name = '';
    public $description = '';
    public $viticulturist_id = '';
    public $area = '';
    public $active = true;
    public $autonomous_community_id = '';
    public $province_id = '';
    public $municipality_id = '';
    public $sigpac_use = [];
    public $ndvi_alert_threshold = 0.30;
    public $alert_email_enabled = false;
    public $tenure_regime = 'propiedad';
    public $site_name = '';
    public $valley = '';
    public $code_parcel = '';
    public $soil_type = '';
    public $orientation = '';
    public $maximum_yield_kg_ha = '';
    public $degree_day_base = '';

    public function mount()
    {
        if (!Auth::user()->can('create', Plot::class)) {
            abort(403);
        }

        // Auto-asignar viticultor si es viticulturist
        // Si es viticultor y no puede seleccionar otros viticultores, se auto-asigna
        if (Auth::user()->isViticulturist()) {
            if (!$this->canSelectViticulturist()) {
                $this->viticulturist_id = Auth::id();
            }

            // Pre-rellenar parámetros agronómicos desde configuración del viticultor
            $settings = ViticulturistSetting::forUser(Auth::id());
            if ($settings?->default_limit_kg_per_ha) {
                $this->maximum_yield_kg_ha = $settings->default_limit_kg_per_ha;
            }
            if ($settings?->degree_day_base) {
                $this->degree_day_base = $settings->degree_day_base;
            }
        }
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'nullable|numeric|min:0',
            'active' => 'boolean',
            'ndvi_alert_threshold' => 'required|numeric|min:0|max:1',
            'alert_email_enabled' => 'boolean',
            'tenure_regime' => 'required|string|in:propiedad,arrendamiento,aparceria,cesion_uso,otros',
            'site_name' => 'nullable|string|max:255',
            'valley' => 'nullable|string|max:255',
            'code_parcel' => 'nullable|string|max:50',
            'soil_type' => 'nullable|string|in:arenoso,arcilloso,limoso,franco,franco-arenoso,franco-arcilloso,franco-limoso,pedregoso',
            'orientation' => 'nullable|string|in:N,NE,E,SE,S,SO,O,NO',
            'maximum_yield_kg_ha' => 'nullable|numeric|min:0',
            'degree_day_base' => 'nullable|numeric|min:0|max:30',
        ];

        // Viticultor es requerido si el usuario tiene rol que puede seleccionar viticultores
        if (in_array(Auth::user()->role, ['admin', 'supervisor', 'winery', 'viticulturist'])) {
            $rules['viticulturist_id'] = 'required|exists:users,id';
        }

        if ($this->canSelectLocation()) {
            $rules['autonomous_community_id'] = 'required|exists:autonomous_communities,id';
            $rules['province_id'] = 'required|exists:provinces,id';
            $rules['municipality_id'] = 'required|exists:municipalities,id';
        }

        if ($this->canSelectSigpac()) {
            $rules['sigpac_use'] = 'required|array|min:1';
            $rules['sigpac_use.*'] = 'exists:sigpac_use,id';
        }

        return $rules;
    }

    public function updatedAutonomousCommunityId($value)
    {
        // Resetear provincia y municipio cuando cambia la comunidad autónoma
        $this->province_id = '';
        $this->municipality_id = '';
    }

    public function updatedProvinceId($value)
    {
        // Resetear municipio cuando cambia la provincia
        $this->municipality_id = '';
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'area' => $this->area ?: null,
                'active' => $this->active,
                'ndvi_alert_threshold' => $this->ndvi_alert_threshold,
                'alert_email_enabled' => $this->alert_email_enabled,
                'tenure_regime' => $this->tenure_regime,
                'site_name' => $this->site_name ?: null,
                'valley' => $this->valley ?: null,
                'code_parcel' => $this->code_parcel ?: null,
                'soil_type' => $this->soil_type ?: null,
                'orientation' => $this->orientation ?: null,
                'maximum_yield_kg_ha' => $this->maximum_yield_kg_ha ?: null,
                'degree_day_base' => $this->degree_day_base ?: null,
            ];

            if ($this->canSelectViticulturist() && $this->viticulturist_id) {
                $user = Auth::user();
                $canAssign = false;

                if ($user->isWinery()) {
                    $canAssign = \App\Models\WineryViticulturist::where('viticulturist_id', $this->viticulturist_id)
                        ->where('winery_id', $user->id)
                        ->where('source', \App\Models\WineryViticulturist::SOURCE_OWN)
                        ->where('assigned_by', $user->id)
                        ->exists();
                } elseif ($user->isViticulturist()) {
                    $canAssign = $user->canEditViticulturist($this->viticulturist_id);
                } else {
                    $canAssign = true;  // Admin y supervisor
                }

                if (!$canAssign) {
                    throw ValidationException::withMessages([
                        'viticulturist_id' => 'Solo puedes asignar parcelas a viticultores que has creado.',
                    ]);
                }

                $data['viticulturist_id'] = $this->viticulturist_id;
            } elseif (Auth::user()->isViticulturist()) {
                // Auto-asignar viticultor si es viticulturist y no puede seleccionar o no seleccionó ninguno
                $data['viticulturist_id'] = Auth::id();
            }

            if ($this->canSelectLocation()) {
                $data['autonomous_community_id'] = $this->autonomous_community_id;
                $data['province_id'] = $this->province_id;
                $data['municipality_id'] = $this->municipality_id;
            }

            $plot = Plot::create($data);

            // Sincronizar relaciones many-to-many
            if ($this->canSelectSigpac() && !empty($this->sigpac_use)) {
                $plot->sigpacUses()->sync($this->sigpac_use);
            }

            DB::commit();

            $this->toastSuccess('Parcela creada correctamente.');
            $indexRoute = Auth::user()->isWinery() ? 'winery.plots.index' : 'plots.index';
            return $this->redirect(route($indexRoute), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();

            // Registrar la excepción completa para debugging
            Log::error('Error al crear parcela: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'data' => $data ?? [],
                'exception' => $e
            ]);

            $this->toastError('Error inesperado al crear la parcela. Por favor, inténtalo de nuevo.');

            return;
        }
    }

    public function render()
    {
        return view('livewire.plots.create', [
            // Usos SIGPAC para el select múltiple
            // ✅ OPTIMIZACIÓN: Solo campos necesarios para select
            'sigpacUses' => SigpacUse::select(['id', 'code', 'description'])
                ->orderBy('code')
                ->get(),

            // ✅ OPTIMIZACIÓN: Solo campos necesarios para selects
            'autonomousCommunities' => AutonomousCommunity::select(['id', 'name', 'code'])
                ->orderBy('name')
                ->get(),
            'provinces' => $this->autonomous_community_id
                ? Province::select(['id', 'name', 'autonomous_community_id'])
                    ->where('autonomous_community_id', $this->autonomous_community_id)
                    ->orderBy('name')
                    ->get()
                : collect(),
            'municipalities' => $this->province_id
                ? Municipality::select(['id', 'name', 'province_id'])
                    ->where('province_id', $this->province_id)
                    ->orderBy('name')
                    ->get()
                : collect(),
        ])->layout('layouts.app');
    }
}
