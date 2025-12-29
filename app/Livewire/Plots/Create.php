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

    public function mount()
    {
        if (!Auth::user()->can('create', Plot::class)) {
            abort(403);
        }

        // (winery_id removed) no auto-assign here

        // Auto-asignar viticultor si es viticulturist
        // Si es viticultor y no puede seleccionar otros viticultores, se auto-asigna
        if (Auth::user()->isViticulturist()) {
            if (!$this->canSelectViticulturist()) {
                // Si NO puede seleccionar viticultores (no tiene viticultores creados), se auto-asigna
                $this->viticulturist_id = Auth::id();
            }
            // Si puede seleccionar, el campo queda vacío para que elija (pero es requerido)
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
        ];

        // Validar solo si el campo es visible (winery_id removed)

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
            ];

            // No asignar `winery_id` — columna eliminada, la pista de pertenencia es `viticulturist_id`.

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
            return $this->redirect(route('plots.index'), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();

            // Registrar la excepción completa para debugging
            Log::error('Error al crear parcela: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'data' => $data ?? [],
                'exception' => $e
            ]);

            // Mostrar el mensaje de error real en la UI temporalmente para depuración
            // Nota: esto debe revertirse en producción una vez esté resuelto.
            $this->addError('general', $e->getMessage());

            return;
        }
    }

    public function render()
    {
        return view('livewire.plots.create', [
            // Usos SIGPAC para el select múltiple
            'sigpacUses' => SigpacUse::orderBy('code')->get(),

            // Comunidades autónomas y ubicación jerárquica
            'autonomousCommunities' => AutonomousCommunity::orderBy('name')->get(),
            'provinces' => $this->autonomous_community_id
                ? Province::where('autonomous_community_id', $this->autonomous_community_id)
                    ->orderBy('name')
                    ->get()
                : collect(),
            'municipalities' => $this->province_id
                ? Municipality::where('province_id', $this->province_id)
                    ->orderBy('name')
                    ->get()
                : collect(),
        ])->layout('layouts.app');
    }
}
