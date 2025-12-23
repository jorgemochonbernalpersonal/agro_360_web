<?php

namespace App\Livewire\Plots;

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

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $search = '';
    public $activeFilter = '';

    protected $queryString = ['search', 'activeFilter'];

    public function render()
    {
        $query = Plot::forUser(Auth::user())
            ->select([
                'id',
                'name',
                'description',
                'area',
                'active',
                // `winery_id` eliminado: la propiedad se deduce por viticultor
                'viticulturist_id',
                'municipality_id',
                'created_at',
                'updated_at',
            ])
            ->with([
                // 'winery:id,name', // relación ya no tiene columna física en plots
                'viticulturist:id,name',
                'municipality:id,name,province_id',
                'municipality.province:id,name',
                'sigpacCodes:id,code',
                'multiplePlotSigpacs:plot_id,sigpac_code_id,plot_geometry_id'
            ]);

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->whereRaw('LOWER(name) LIKE ?', [$search]);
        }

        if ($this->activeFilter !== '') {
            $query->where('active', $this->activeFilter === '1');
        }

        $plots = $query->latest()->paginate(10);

        return view('livewire.plots.index', [
            'plots' => $plots,
        ])->layout('layouts.app', [
            'title' => 'Gestión de Parcelas - Agro365',
            'description' => 'Administra y visualiza todas tus parcelas agrícolas. Control total de viñedos con integración SIGPAC.',
        ]);
    }

    public function delete(Plot $plot)
    {
        if (!Auth::user()->can('delete', $plot)) {
            abort(403);
        }

        $plot->delete();
        $this->toastSuccess('Parcela eliminada correctamente.');
    }

    public function generateMap($plotId)
    {
        $plot = Plot::findOrFail($plotId);
        
        if (!Auth::user()->can('update', $plot)) {
            $this->toastError('No tienes permiso para modificar esta parcela.');
            return;
        }

        $sigpacCodes = $plot->sigpacCodes;
        
        if ($sigpacCodes->isEmpty()) {
            $this->toastError('Esta parcela no tiene códigos SIGPAC asociados.');
            return;
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            foreach ($sigpacCodes as $sigpacCode) {
                try {
                    $wkt = $this->fetchCoordinatesFromSigpacApi($sigpacCode);
                    
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
                        ->where('sigpac_code_id', $sigpacCode->id)
                        ->first();

                    if ($mps) {
                        $mps->plot_geometry_id = $geometryId;
                        $mps->updated_at = now();
                        $mps->save();
                    } else {
                        MultipartPlotSigpac::create([
                            'plot_id' => $plotId,
                            'sigpac_code_id' => $sigpacCode->id,
                            'plot_geometry_id' => $geometryId,
                        ]);
                    }

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
                // Forzar recarga de la vista
                $this->dispatch('$refresh');
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
            $this->toastError('Error al generar los mapas. Por favor, intenta de nuevo.');
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
