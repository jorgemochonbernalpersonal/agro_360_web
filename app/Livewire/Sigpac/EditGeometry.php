<?php


namespace App\Livewire\Sigpac;

use App\Models\Plot;
use App\Models\SigpacCode;
use App\Models\PlotGeometry;
use App\Models\MultipartPlotSigpac;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditGeometry extends Component
{
    use WithToastNotifications;

    public $sigpacId;
    public $plotId;
    public $geometryId = null;
    public $coordinates = [];
    public $showMap = false;
    public $viewOnly = false;

    public function mount($sigpacId, $plotId = null)
    {
        $this->sigpacId = $sigpacId;
        $this->plotId = $plotId;
        
        // Verificar si viene en modo solo lectura desde query string
        $this->viewOnly = request()->query('view') === 'true';
        
        // Si hay plotId, buscar geometría existente
        if ($plotId) {
            $mps = MultipartPlotSigpac::where('plot_id', $plotId)
                ->where('sigpac_code_id', $sigpacId)
                ->whereNotNull('plot_geometry_id')
                ->with('plotGeometry')
                ->first();
            
            if ($mps && $mps->plotGeometry) {
                $this->geometryId = $mps->plot_geometry_id;
                $this->coordinates = $mps->plotGeometry->getCoordinatesAsArray();
                // Si es modo solo lectura, no mostrar editor
                if ($this->viewOnly) {
                    $this->showMap = false; // No mostrar editor, solo vista
                }
            }
        } else {
            // Si no hay plotId pero hay geometría, también mostrar en modo solo lectura
            $mps = MultipartPlotSigpac::where('sigpac_code_id', $sigpacId)
                ->whereNotNull('plot_geometry_id')
                ->with('plotGeometry')
                ->first();
            
            if ($mps && $mps->plotGeometry) {
                $this->geometryId = $mps->plot_geometry_id;
                $this->coordinates = $mps->plotGeometry->getCoordinatesAsArray();
                if ($this->viewOnly) {
                    $this->showMap = false;
                }
            }
        }
    }

    public function save()
    {
        // Validar coordenadas con Livewire
        try {
            $this->validate([
                'coordinates' => 'required|array|min:3',
                'coordinates.*.lat' => 'required|numeric|between:-90,90',
                'coordinates.*.lng' => 'required|numeric|between:-180,180',
            ], [
                'coordinates.required' => 'Las coordenadas son obligatorias.',
                'coordinates.min' => 'Se necesitan al menos 3 puntos para crear un polígono.',
                'coordinates.*.lat.required' => 'La latitud es obligatoria.',
                'coordinates.*.lat.numeric' => 'La latitud debe ser un valor numérico.',
                'coordinates.*.lat.between' => 'La latitud debe estar entre -90 y 90.',
                'coordinates.*.lng.required' => 'La longitud es obligatoria.',
                'coordinates.*.lng.numeric' => 'La longitud debe ser un valor numérico.',
                'coordinates.*.lng.between' => 'La longitud debe estar entre -180 y 180.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->toastError('Error de validación: ' . $e->validator->errors()->first());
            return;
        }

        if (!$this->plotId) {
            $this->toastError('Debes seleccionar una parcela.');
            return;
        }

        $plot = Plot::findOrFail($this->plotId);
        
        if (!Auth::user()->can('update', $plot)) {
            $this->toastError('No tienes permiso para modificar esta parcela.');
            return;
        }

        try {
            DB::beginTransaction();

            // Construir WKT con validación estricta (usando el mismo código seguro del modelo)
            $points = $this->coordinates;
            $firstPoint = $points[0];
            $lastPoint = end($points);
            if ($firstPoint['lat'] != $lastPoint['lat'] || $firstPoint['lng'] != $lastPoint['lng']) {
                $points[] = $firstPoint;
            }
            
            // Validar y construir WKT con tipos estrictos
            $wktPoints = collect($points)->map(function($point) {
                // Validar y castear a float para prevenir SQL injection
                $lng = filter_var($point['lng'], FILTER_VALIDATE_FLOAT);
                $lat = filter_var($point['lat'], FILTER_VALIDATE_FLOAT);
                
                if ($lng === false || $lat === false) {
                    throw new \InvalidArgumentException('Coordenadas inválidas: deben ser valores numéricos.');
                }
                
                return "$lng $lat";
            })->join(', ');
            
            $wkt = "POLYGON(($wktPoints))";

            // Crear o actualizar geometría usando prepared statements seguros
            if ($this->geometryId) {
                $geometryId = $this->geometryId;
                DB::statement(
                    "UPDATE plot_geometry SET 
                        coordinates = ST_GeomFromText(?, 4326),
                        centroid = ST_Centroid(ST_GeomFromText(?, 4326)),
                        updated_at = NOW()
                    WHERE id = ?",
                    [$wkt, $wkt, $geometryId]
                );
            } else {
                // Insertar con prepared statement
                $geometryId = DB::table('plot_geometry')->insertGetId([
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Actualizar geometría con prepared statement
                DB::statement(
                    "UPDATE plot_geometry SET 
                        coordinates = ST_GeomFromText(?, 4326),
                        centroid = ST_Centroid(ST_GeomFromText(?, 4326))
                    WHERE id = ?",
                    [$wkt, $wkt, $geometryId]
                );
            }

            // Crear o actualizar relación plot-sigpacCode-geometry
            MultipartPlotSigpac::updateOrCreate(
                [
                    'plot_id' => $this->plotId,
                    'sigpac_code_id' => $this->sigpacId,
                    'plot_geometry_id' => $geometryId,
                ],
                [
                    'updated_at' => now(),
                ]
            );

            DB::commit();

            $this->geometryId = $geometryId;
            // Recargar coordenadas desde BD
            $geometry = PlotGeometry::find($geometryId);
            if ($geometry) {
                $this->coordinates = $geometry->getCoordinatesAsArray();
            }
            
            $this->toastSuccess('Geometría guardada correctamente.');
            $this->showMap = false;
            
            // Emitir evento para refrescar vista
            $this->dispatch('geometry-saved');
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            \Log::warning('Invalid geometry data', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            $this->toastError($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving geometry', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
            $this->toastError('Error al guardar la geometría. Por favor, intenta de nuevo.');
        }
    }

    public function delete()
    {
        if (!$this->geometryId) {
            return;
        }

        try {
            DB::beginTransaction();

            // Eliminar relación
            MultipartPlotSigpac::where('plot_id', $this->plotId)
                ->where('sigpac_code_id', $this->sigpacId)
                ->where('plot_geometry_id', $this->geometryId)
                ->delete();

            // Eliminar geometría
            PlotGeometry::where('id', $this->geometryId)->delete();

            DB::commit();

            $this->geometryId = null;
            $this->coordinates = [];
            $this->toastSuccess('Geometría eliminada correctamente.');
            $this->showMap = false;
            
            $this->dispatch('geometry-deleted');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->toastError('Error al eliminar la geometría.');
        }
    }

    public function render()
    {
        $sigpacCode = SigpacCode::findOrFail($this->sigpacId);
        $plot = $this->plotId ? Plot::find($this->plotId) : null;
        
        // Obtener parcelas del usuario que tienen este código SIGPAC
        $user = Auth::user();
        $availablePlots = Plot::forUser($user)
            ->get()
            ->filter(function($plot) use ($sigpacCode) {
                // Incluir si ya tiene este código SIGPAC
                return $plot->sigpacCodes->contains('id', $sigpacCode->id) ||
                       $plot->sigpacCodesOld->contains('id', $sigpacCode->id);
            });

        return view('livewire.sigpac.edit-geometry', [
            'sigpac' => $sigpacCode,
            'plot' => $plot,
            'availablePlots' => $availablePlots,
        ]);
    }
}

