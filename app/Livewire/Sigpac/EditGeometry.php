<?php

namespace App\Livewire\Sigpac;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\MultipartPlotSigpac;
use App\Models\Plot;
use App\Models\PlotGeometry;
use App\Models\SigpacCode;
use App\Services\SigpacGeometryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

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

        $this->viewOnly = request()->query('view') === 'true';

        if ($plotId) {
            $mps = MultipartPlotSigpac::where('plot_id', $plotId)
                ->where('sigpac_code_id', $sigpacId)
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
        } else {
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
        try {
            $this->validate([
                'coordinates' => 'required|array|min:3',
                'coordinates.*.lat' => 'required|numeric|between:-90,90',
                'coordinates.*.lng' => 'required|numeric|between:-180,180',
            ], [
                'coordinates.required' => __('Las coordenadas son obligatorias.'),
                'coordinates.min' => __('Se necesitan al menos 3 puntos para crear un polígono.'),
                'coordinates.*.lat.required' => __('La latitud es obligatoria.'),
                'coordinates.*.lat.numeric' => __('La latitud debe ser un valor numérico.'),
                'coordinates.*.lat.between' => __('La latitud debe estar entre -90 y 90.'),
                'coordinates.*.lng.required' => __('La longitud es obligatoria.'),
                'coordinates.*.lng.numeric' => __('La longitud debe ser un valor numérico.'),
                'coordinates.*.lng.between' => __('La longitud debe estar entre -180 y 180.'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->toastError(__('Error de validación: :error', ['error' => $e->validator->errors()->first()]));

            return;
        }

        if (! $this->plotId) {
            $this->toastError(__('Debes seleccionar una parcela.'));

            return;
        }

        $plot = Plot::findOrFail($this->plotId);

        if (! Auth::user()->can('update', $plot)) {
            $this->toastError(__('No tienes permiso para modificar esta parcela.'));

            return;
        }

        try {
            DB::beginTransaction();

            $points = $this->coordinates;
            $firstPoint = $points[0];
            $lastPoint = end($points);
            if ($firstPoint['lat'] != $lastPoint['lat'] || $firstPoint['lng'] != $lastPoint['lng']) {
                $points[] = $firstPoint;
            }

            $wktPoints = collect($points)->map(function ($point) {
                $lng = filter_var($point['lng'], FILTER_VALIDATE_FLOAT);
                $lat = filter_var($point['lat'], FILTER_VALIDATE_FLOAT);

                if ($lng === false || $lat === false) {
                    throw new \InvalidArgumentException(__('Coordenadas inválidas: deben ser valores numéricos.'));
                }

                return "$lng $lat";
            })->join(', ');

            $wkt = "POLYGON(($wktPoints))";
            $service = app(SigpacGeometryService::class);

            if ($this->geometryId) {
                $service->updateGeometry($this->geometryId, $wkt);
                $geometryId = $this->geometryId;
            } else {
                $geometryId = $service->upsertGeometry($this->plotId, SigpacCode::findOrFail($this->sigpacId), $wkt);
            }

            DB::commit();

            $this->geometryId = $geometryId;
            $geometry = PlotGeometry::find($geometryId);
            if ($geometry) {
                $this->coordinates = $geometry->getCoordinatesAsArray();
            }

            $this->toastSuccess(__('Geometría guardada correctamente.'));
            $this->showMap = false;
            $this->dispatch('geometry-saved');
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            Log::warning('Invalid geometry data', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            $this->toastError($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving geometry', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            $this->toastError(__('Error al guardar la geometría. Por favor, intenta de nuevo.'));
        }
    }

    public function generateMapFromSigpac()
    {
        if (! $this->plotId) {
            $this->toastError(__('Debes seleccionar una parcela.'));

            return;
        }

        $plot = Plot::find($this->plotId);

        if (! $plot || ! Auth::user()->can('update', $plot)) {
            $this->toastError(__('No tienes permiso para modificar esta parcela.'));

            return;
        }

        $sigpacCodes = $plot->sigpacCodes;

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

                    if (! $wkt) {
                        $errorCount++;
                        $errors[] = "No se pudieron obtener coordenadas para el código {$sigpacCode->code}";

                        continue;
                    }

                    if (! preg_match(SigpacGeometryService::WKT_PATTERN, $wkt)) {
                        $errorCount++;
                        $errors[] = "Formato de coordenadas inválido para el código {$sigpacCode->code}";

                        continue;
                    }

                    $service->upsertGeometry($this->plotId, $sigpacCode, $wkt);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Error procesando código {$sigpacCode->code}: ".$e->getMessage();
                    Log::error('Error generating map for sigpac code', [
                        'sigpac_code_id' => $sigpacCode->id,
                        'plot_id' => $this->plotId,
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
            }

            if ($errorCount > 0) {
                $this->toastError('Error al generar '.$errorCount.' mapa(s). '.implode(' ', array_slice($errors, 0, 3)));
            }

            if ($this->sigpacId && $successCount > 0) {
                $mps = MultipartPlotSigpac::where('plot_id', $this->plotId)
                    ->where('sigpac_code_id', $this->sigpacId)
                    ->whereNotNull('plot_geometry_id')
                    ->with('plotGeometry')
                    ->first();

                if ($mps && $mps->plotGeometry) {
                    $this->geometryId = $mps->plot_geometry_id;
                    $this->coordinates = $mps->plotGeometry->getCoordinatesAsArray();
                }
            }

            $this->dispatch('geometry-saved');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating maps from SIGPAC', ['plot_id' => $this->plotId, 'error' => $e->getMessage()]);
            $this->toastError(__('Error al generar los mapas. Por favor, intenta de nuevo.'));
        }
    }

    public function delete()
    {
        if (! $this->geometryId) {
            return;
        }

        try {
            DB::beginTransaction();

            MultipartPlotSigpac::where('plot_id', $this->plotId)
                ->where('sigpac_code_id', $this->sigpacId)
                ->where('plot_geometry_id', $this->geometryId)
                ->delete();

            PlotGeometry::where('id', $this->geometryId)->delete();

            DB::commit();

            $this->geometryId = null;
            $this->coordinates = [];
            $this->toastSuccess(__('Geometría eliminada correctamente.'));
            $this->showMap = false;
            $this->dispatch('geometry-deleted');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->toastError(__('Error al eliminar la geometría.'));
        }
    }

    public function render()
    {
        $sigpacCode = SigpacCode::findOrFail($this->sigpacId);
        $plot = $this->plotId ? Plot::find($this->plotId) : null;

        $user = Auth::user();
        $availablePlots = Plot::forUser($user)
            ->get()
            ->filter(function ($plot) use ($sigpacCode) {
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
