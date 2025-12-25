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

    public $currentTab = 'active'; // 'active', 'inactive', 'statistics'
    public $search = '';
    public $yearFilter;

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search' => ['except' => ''],
        'yearFilter' => ['as' => 'year'],
    ];

    public function mount()
    {
        $this->yearFilter = $this->yearFilter ?? now()->year;
    }

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function toggleActive($plotId)
    {
        $user = Auth::user();
        $plot = Plot::forUser($user)->findOrFail($plotId);
        
        if (!$user->can('update', $plot)) {
            abort(403);
        }
        
        $wasActive = $plot->active;
        $newActiveState = !$wasActive;
        
        $plot->update([
            'active' => $newActiveState
        ]);

        if ($newActiveState) {
            $this->toastSuccess('Parcela activada exitosamente.');
            // Si estamos en el tab de inactivos, cambiar al tab de activos para ver el cambio
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess('Parcela desactivada exitosamente.');
            // Si estamos en el tab de activos, cambiar al tab de inactivos para ver el cambio
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

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

        // Filtro por tab (activo/inactivo)
        if ($this->currentTab === 'active') {
            $query->where('active', true); // Activos
        } elseif ($this->currentTab === 'inactive') {
            $query->where('active', false); // Inactivos
        }

        $plots = $query->latest()->paginate(10);

        // Estadísticas básicas
        $baseQuery = Plot::forUser(Auth::user());
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('active', true)->count(),
            'inactive' => (clone $baseQuery)->where('active', false)->count(),
        ];

        // Estadísticas avanzadas (solo para tab de estadísticas)
        $advancedStats = [];
        if ($this->currentTab === 'statistics') {
            $advancedStats = $this->getAdvancedStatistics(Auth::user());
        }

        return view('livewire.plots.index', [
            'plots' => $plots,
            'stats' => $stats,
            'advancedStats' => $advancedStats,
        ])->layout('layouts.app', [
            'title' => 'Gestión de Parcelas - Agro365',
            'description' => 'Administra y visualiza todas tus parcelas agrícolas. Control total de viñedos con integración SIGPAC.',
        ]);
    }

    private function getAdvancedStatistics($user)
    {
        $year = $this->yearFilter;
        $allPlots = Plot::forUser($user)->get();
        
        // Superficie total
        $totalSurface = $allPlots->sum('area');
        $eligibleSurface = $allPlots->sum('pac_eligible_area') ?: $totalSurface;
        $nonEligibleSurface = $allPlots->sum('non_eligible_area');
        $eligibilityPercentage = $totalSurface > 0 ? ($eligibleSurface / $totalSurface) * 100 : 0;
        
        // Parcelas bloqueadas
        $lockedPlots = $allPlots->where('is_locked', true)->count();
        $unlockedPlots = $allPlots->where('is_locked', false)->count();
        
        // Distribución por régimen de tenencia
        $tenureStats = $allPlots->groupBy('tenure_regime')->map(function ($group) {
            return [
                'count' => $group->count(),
                'surface' => $group->sum('area'),
            ];
        });
        
        // Parcelas con/sin códigos SIGPAC
        $withSigpac = $allPlots->filter(function($plot) {
            return $plot->sigpacCodes && $plot->sigpacCodes->count() > 0;
        })->count();
        $withoutSigpac = $allPlots->count() - $withSigpac;
        
        // Distribución por provincia
        $provinceStats = $allPlots->groupBy('province_id')->map(function ($group) {
            return [
                'count' => $group->count(),
                'surface' => $group->sum('area'),
                'province_name' => $group->first()->province->name ?? 'Sin provincia',
            ];
        })->sortByDesc('surface')->take(10);
        
        // Nuevas parcelas por mes (últimos 12 meses)
        $newPlotsByMonth = collect(range(11, 0))->map(function($monthsAgo) use ($user) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M'),
                'count' => Plot::forUser($user)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        });
        
        // Parcelas con plantaciones
        $withPlantings = $allPlots->filter(function($plot) {
            return $plot->plantings && $plot->plantings->count() > 0;
        })->count();
        $withoutPlantings = $allPlots->count() - $withPlantings;
        
        // Superficie media por parcela
        $avgSurfacePerPlot = $allPlots->count() > 0 ? $allPlots->avg('area') : 0;
        
        return [
            'totalSurface' => $totalSurface,
            'eligibleSurface' => $eligibleSurface,
            'nonEligibleSurface' => $nonEligibleSurface,
            'eligibilityPercentage' => $eligibilityPercentage,
            'lockedPlots' => $lockedPlots,
            'unlockedPlots' => $unlockedPlots,
            'tenureStats' => $tenureStats,
            'withSigpac' => $withSigpac,
            'withoutSigpac' => $withoutSigpac,
            'provinceStats' => $provinceStats,
            'newPlotsByMonth' => $newPlotsByMonth,
            'withPlantings' => $withPlantings,
            'withoutPlantings' => $withoutPlantings,
            'avgSurfacePerPlot' => $avgSurfacePerPlot,
        ];
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
