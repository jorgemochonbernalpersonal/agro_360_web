<?php

namespace App\Livewire\Sigpac;

use App\Models\Plot;
use App\Models\Sigpac;
use App\Models\PlotGeometry;
use App\Models\MultiplePlotSigpac;
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

    public function mount($sigpacId, $plotId = null)
    {
        $this->sigpacId = $sigpacId;
        $this->plotId = $plotId;
        
        // Si hay plotId, buscar geometría existente
        if ($plotId) {
            $mps = MultiplePlotSigpac::where('plot_id', $plotId)
                ->where('sigpac_id', $sigpacId)
                ->whereNotNull('plot_geometry_id')
                ->with('plotGeometry')
                ->first();
            
            if ($mps && $mps->plotGeometry) {
                $this->geometryId = $mps->plot_geometry_id;
                $this->coordinates = $mps->plotGeometry->getCoordinatesAsArray();
            }
        }
    }

    public function save()
    {
        if (empty($this->coordinates) || count($this->coordinates) < 3) {
            $this->toastError('Se necesitan al menos 3 puntos para crear un polígono.');
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

            // Construir WKT
            $points = $this->coordinates;
            $firstPoint = $points[0];
            $lastPoint = end($points);
            if ($firstPoint['lat'] != $lastPoint['lat'] || $firstPoint['lng'] != $lastPoint['lng']) {
                $points[] = $firstPoint;
            }
            
            $wktPoints = collect($points)->map(function($point) {
                return "{$point['lng']} {$point['lat']}";
            })->join(', ');
            
            $wkt = "POLYGON(($wktPoints))";
            $wktEscaped = str_replace("'", "''", $wkt);

            // Crear o actualizar geometría usando SQL directo para MySQL
            if ($this->geometryId) {
                $geometryId = $this->geometryId;
                DB::statement(
                    "UPDATE plot_geometry SET 
                        coordinates = ST_GeomFromText(?, 4326),
                        centroid = ST_Centroid(ST_GeomFromText(?, 4326)),
                        updated_at = NOW()
                    WHERE id = ?",
                    [$wktEscaped, $wktEscaped, $geometryId]
                );
            } else {
                $geometryId = DB::table('plot_geometry')->insertGetId([
                    'coordinates' => DB::raw("ST_GeomFromText('$wktEscaped', 4326)"),
                    'centroid' => DB::raw("ST_Centroid(ST_GeomFromText('$wktEscaped', 4326))"),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Crear o actualizar relación plot-sigpac-geometry
            MultiplePlotSigpac::updateOrCreate(
                [
                    'plot_id' => $this->plotId,
                    'sigpac_id' => $this->sigpacId,
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
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving geometry', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->toastError('Error al guardar la geometría: ' . $e->getMessage());
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
            MultiplePlotSigpac::where('plot_id', $this->plotId)
                ->where('sigpac_id', $this->sigpacId)
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
        $sigpac = Sigpac::findOrFail($this->sigpacId);
        $plot = $this->plotId ? Plot::find($this->plotId) : null;
        
        // Obtener parcelas del usuario que tienen este sigpac o pueden tenerlo
        $user = Auth::user();
        $availablePlots = Plot::forUser($user)
            ->get()
            ->filter(function($plot) use ($sigpac) {
                // Incluir si ya tiene este sigpac o si tiene sigpac codes relacionados
                return $plot->sigpacs->contains('id', $sigpac->id) ||
                       $plot->sigpacCodes->contains(function($code) use ($sigpac) {
                           return str_contains($code->code, $sigpac->full_code) ||
                                  str_contains($sigpac->full_code, $code->code);
                       });
            });

        return view('livewire.sigpac.edit-geometry', [
            'sigpac' => $sigpac,
            'plot' => $plot,
            'availablePlots' => $availablePlots,
        ])->layout('layouts.app');
    }
}

