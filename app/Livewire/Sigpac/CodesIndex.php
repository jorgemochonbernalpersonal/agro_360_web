<?php

namespace App\Livewire\Sigpac;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\MultipartPlotSigpac;
use App\Models\Plot;
use App\Models\PlotGeometry;
use App\Models\SigpacCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class CodesIndex extends Component
{
    use WithPagination, WithToastNotifications;

    public $search = '';

    protected $queryString = ['search'];

    public function render()
    {
        $user = Auth::user();
        
        // Obtener IDs de parcelas que el usuario puede ver
        $plotIds = Plot::forUser($user)->pluck('id');
        
        $codes = SigpacCode::query()
            ->whereHas('plots', function($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds);
            })
            ->when($this->search, function($query) {
                $search = '%' . strtolower($this->search) . '%';
                $query->whereRaw('LOWER(code) LIKE ?', [$search]);
            })
            ->with(['plots' => function($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds)->limit(1);
            }])
            ->withCount(['plots' => function($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds);
            }])
            ->orderBy('code')
            ->paginate(10);

        return view('livewire.sigpac.codes-index', [
            'codes' => $codes,
        ])->layout('layouts.app', [
            'title' => 'Códigos SIGPAC - Agro365',
            'description' => 'Gestiona los códigos de identificación SIGPAC de tus parcelas. Integración completa con el sistema SIGPAC para cumplimiento normativo.',
        ]);
    }

    public function generateMap($sigpacCodeId, $plotId)
    {
        $plot = Plot::findOrFail($plotId);
        $sigpacCode = SigpacCode::findOrFail($sigpacCodeId);
        
        if (!Auth::user()->can('update', $plot)) {
            $this->toastError('No tienes permiso para modificar esta parcela.');
            return;
        }

        $sigpacCodes = $plot->sigpacCodes->where('id', $sigpacCodeId);
        
        if ($sigpacCodes->isEmpty()) {
            $this->toastError('Este código SIGPAC no está asociado a esta parcela.');
            return;
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            $wkt = $this->fetchCoordinatesFromSigpacApi($sigpacCode);
            
            if (!$wkt) {
                $this->toastError("No se pudieron obtener coordenadas para el código {$sigpacCode->code}");
                return;
            }

            if (!preg_match('/^(POLYGON|MULTIPOLYGON|LINESTRING|POINT)\s*\(.+\)$/i', $wkt)) {
                $this->toastError("Formato de coordenadas inválido para el código {$sigpacCode->code}");
                return;
            }

            $geometryId = DB::table('plot_geometry')->insertGetId([
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::statement(
                'UPDATE plot_geometry SET 
                    coordinates = ST_GeomFromText(?, 4326),
                    centroid = ST_Centroid(ST_GeomFromText(?, 4326))
                WHERE id = ?',
                [$wkt, $wkt, $geometryId]
            );

            $mps = MultipartPlotSigpac::where('plot_id', $plotId)
                ->where('sigpac_code_id', $sigpacCodeId)
                ->first();

            if ($mps) {
                $mps->plot_geometry_id = $geometryId;
                $mps->updated_at = now();
                $mps->save();
            } else {
                MultipartPlotSigpac::create([
                    'plot_id' => $plotId,
                    'sigpac_code_id' => $sigpacCodeId,
                    'plot_geometry_id' => $geometryId,
                ]);
            }

            DB::commit();
            $this->toastSuccess('Mapa generado correctamente.');
            // Forzar recarga de la vista
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating map from SIGPAC', [
                'plot_id' => $plotId,
                'sigpac_code_id' => $sigpacCodeId,
                'error' => $e->getMessage(),
            ]);
            $this->toastError('Error al generar el mapa. Por favor, intenta de nuevo.');
        }
    }

    private function fetchCoordinatesFromSigpacApi(SigpacCode $sigpacCode): ?string
    {
        try {
            $url = sprintf(
                'https://sigpac-hubcloud.es/servicioconsultassigpac/query/recinfo/%s/%s/%s/%s/%s/%s/%s.json',
                $sigpacCode->code_province,
                $sigpacCode->code_municipality,
                $sigpacCode->code_aggregate ?? '0',
                $sigpacCode->code_zone,
                $sigpacCode->code_polygon,
                $sigpacCode->code_plot,
                $sigpacCode->code_enclosure
            );

            $httpClient = Http::timeout(10);
            if (app()->environment('local')) {
                $httpClient = $httpClient->withoutVerifying();
            }
            
            $response = $httpClient->get($url);
            
            if ($response->status() !== 200) {
                return null;
            }

            $data = $response->json();
            
            if (!is_array($data) || empty($data) || !isset($data[0]['wkt'])) {
                return null;
            }

            return $data[0]['wkt'];
        } catch (\Exception $e) {
            Log::warning('Error fetching SIGPAC coordinates', [
                'sigpac_code_id' => $sigpacCode->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}

