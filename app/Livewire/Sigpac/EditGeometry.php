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
                    $this->showMap = false;  // No mostrar editor, solo vista
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
            $wktPoints = collect($points)->map(function ($point) {
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
                    'UPDATE plot_geometry SET 
                        coordinates = ST_GeomFromText(?, 4326),
                        centroid = ST_Centroid(ST_GeomFromText(?, 4326)),
                        updated_at = NOW()
                    WHERE id = ?',
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
                    'UPDATE plot_geometry SET 
                        coordinates = ST_GeomFromText(?, 4326),
                        centroid = ST_Centroid(ST_GeomFromText(?, 4326))
                    WHERE id = ?',
                    [$wkt, $wkt, $geometryId]
                );
            }

            // Buscar si existe la relación plot-sigpacCode (incluso sin geometría)
            $mps = MultipartPlotSigpac::where('plot_id', $this->plotId)
                ->where('sigpac_code_id', $this->sigpacId)
                ->first();

            if ($mps) {
                // Si existe, actualizar con el plot_geometry_id
                $mps->plot_geometry_id = $geometryId;
                $mps->updated_at = now();
                $mps->save();
            } else {
                // Si no existe, crear la relación
                MultipartPlotSigpac::create([
                    'plot_id' => $this->plotId,
                    'sigpac_code_id' => $this->sigpacId,
                    'plot_geometry_id' => $geometryId,
                ]);
            }

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
            Log::warning('Invalid geometry data', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            $this->toastError($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving geometry', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
            $this->toastError('Error al guardar la geometría. Por favor, intenta de nuevo.');
        }
    }

    /**
     * Obtener coordenadas desde la API de SIGPAC
     */
    private function fetchCoordinatesFromSigpacApi(SigpacCode $sigpacCode): ?string
    {
        Log::info('fetchCoordinatesFromSigpacApi: Iniciando', [
            'sigpac_code_id' => $sigpacCode->id,
            'code' => $sigpacCode->code,
        ]);

        try {
            // Construir URL de la API SIGPAC
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

            Log::info('fetchCoordinatesFromSigpacApi: URL construida', [
                'url' => $url,
                'province' => $sigpacCode->code_province,
                'municipality' => $sigpacCode->code_municipality,
                'aggregate' => $sigpacCode->code_aggregate ?? '0',
                'zone' => $sigpacCode->code_zone,
                'polygon' => $sigpacCode->code_polygon,
                'plot' => $sigpacCode->code_plot,
                'enclosure' => $sigpacCode->code_enclosure,
            ]);

            // Deshabilitar verificación SSL en desarrollo local (Windows suele tener problemas con certificados)
            $httpClient = Http::timeout(10);
            if (app()->environment('local')) {
                $httpClient = $httpClient->withoutVerifying();
            }

            /** @var \Illuminate\Http\Client\Response $response */
            $response = $httpClient->get($url);

            $status = $response->status();
            Log::info('fetchCoordinatesFromSigpacApi: Respuesta HTTP recibida', [
                'status' => $status,
                'url' => $url,
            ]);

            if ($status !== 200) {
                Log::warning('fetchCoordinatesFromSigpacApi: Status HTTP no es 200', [
                    'status' => $status,
                    'body_preview' => substr($response->body(), 0, 200),
                ]);
                return null;
            }

            $data = $response->json();
            Log::info('fetchCoordinatesFromSigpacApi: JSON parseado', [
                'is_array' => is_array($data),
                'is_empty' => empty($data),
                'has_wkt' => isset($data[0]['wkt']),
                'data_keys' => is_array($data) && !empty($data) ? array_keys($data[0] ?? []) : [],
            ]);

            if (!is_array($data) || empty($data) || !isset($data[0]['wkt'])) {
                Log::warning('fetchCoordinatesFromSigpacApi: Datos inválidos o sin WKT', [
                    'data_type' => gettype($data),
                    'data_is_array' => is_array($data),
                    'data_empty' => empty($data),
                    'data_preview' => is_array($data) ? json_encode(array_slice($data, 0, 1)) : 'not array',
                ]);
                return null;
            }

            $wkt = $data[0]['wkt'];
            Log::info('fetchCoordinatesFromSigpacApi: WKT obtenido', [
                'wkt_length' => strlen($wkt),
                'wkt_preview' => substr($wkt, 0, 100),
            ]);

            return $wkt;
        } catch (\Exception $e) {
            Log::error('fetchCoordinatesFromSigpacApi: Excepción capturada', [
                'sigpac_code_id' => $sigpacCode->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Generar mapa automáticamente desde la API SIGPAC
     * Este método se llama cuando se pulsa "Generar Mapa" desde la vista de parcela
     */
    public function generateMapFromSigpac()
    {
        Log::info('=== generateMapFromSigpac INICIADO ===', [
            'plotId' => $this->plotId,
            'sigpacId' => $this->sigpacId,
            'user_id' => Auth::id(),
        ]);

        if (!$this->plotId) {
            Log::warning('generateMapFromSigpac: No hay plotId');
            $this->toastError('Debes seleccionar una parcela.');
            return;
        }

        Log::info('generateMapFromSigpac: plotId encontrado', ['plotId' => $this->plotId]);

        try {
            $plot = Plot::findOrFail($this->plotId);
            Log::info('generateMapFromSigpac: Plot encontrado', ['plot_id' => $plot->id]);
        } catch (\Exception $e) {
            Log::error('generateMapFromSigpac: Error al buscar plot', [
                'plotId' => $this->plotId,
                'error' => $e->getMessage(),
            ]);
            $this->toastError('Error al buscar la parcela.');
            return;
        }

        if (!Auth::user()->can('update', $plot)) {
            Log::warning('generateMapFromSigpac: Usuario sin permisos', [
                'user_id' => Auth::id(),
                'plot_id' => $plot->id,
            ]);
            $this->toastError('No tienes permiso para modificar esta parcela.');
            return;
        }

        Log::info('generateMapFromSigpac: Permisos validados');

        // Cargar códigos SIGPAC de la parcela
        $sigpacCodes = $plot->sigpacCodes;

        Log::info('generateMapFromSigpac: Códigos SIGPAC cargados', [
            'count' => $sigpacCodes->count(),
            'codes' => $sigpacCodes->pluck('code')->toArray(),
        ]);

        if ($sigpacCodes->isEmpty()) {
            Log::warning('generateMapFromSigpac: No hay códigos SIGPAC');
            $this->toastError('Esta parcela no tiene códigos SIGPAC asociados.');
            return;
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        try {
            DB::beginTransaction();
            Log::info('generateMapFromSigpac: Transacción iniciada');

            foreach ($sigpacCodes as $sigpacCode) {
                Log::info('generateMapFromSigpac: Procesando código SIGPAC', [
                    'sigpac_code_id' => $sigpacCode->id,
                    'code' => $sigpacCode->code,
                ]);

                try {
                    // Obtener coordenadas desde la API
                    Log::info('generateMapFromSigpac: Llamando a fetchCoordinatesFromSigpacApi', [
                        'sigpac_code_id' => $sigpacCode->id,
                    ]);

                    $wkt = $this->fetchCoordinatesFromSigpacApi($sigpacCode);

                    Log::info('generateMapFromSigpac: Respuesta de API recibida', [
                        'sigpac_code_id' => $sigpacCode->id,
                        'wkt_received' => $wkt !== null,
                        'wkt_length' => $wkt ? strlen($wkt) : 0,
                    ]);

                    if (!$wkt) {
                        $errorCount++;
                        $errorMsg = "No se pudieron obtener coordenadas para el código {$sigpacCode->code}";
                        $errors[] = $errorMsg;
                        Log::warning('generateMapFromSigpac: No se obtuvieron coordenadas', [
                            'sigpac_code_id' => $sigpacCode->id,
                            'code' => $sigpacCode->code,
                        ]);
                        continue;
                    }

                    // Validar formato WKT
                    if (!preg_match('/^(POLYGON|MULTIPOLYGON|LINESTRING|POINT)\s*\(.+\)$/i', $wkt)) {
                        $errorCount++;
                        $errorMsg = "Formato de coordenadas inválido para el código {$sigpacCode->code}";
                        $errors[] = $errorMsg;
                        Log::warning('generateMapFromSigpac: Formato WKT inválido', [
                            'sigpac_code_id' => $sigpacCode->id,
                            'wkt_preview' => substr($wkt, 0, 100),
                        ]);
                        continue;
                    }

                    Log::info('generateMapFromSigpac: WKT validado, creando PlotGeometry');

                    // Crear PlotGeometry
                    $geometryId = DB::table('plot_geometry')->insertGetId([
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    Log::info('generateMapFromSigpac: PlotGeometry creado', ['geometry_id' => $geometryId]);

                    // Insertar coordenadas usando prepared statement
                    DB::statement(
                        'UPDATE plot_geometry SET 
                            coordinates = ST_GeomFromText(?, 4326),
                            centroid = ST_Centroid(ST_GeomFromText(?, 4326))
                        WHERE id = ?',
                        [$wkt, $wkt, $geometryId]
                    );

                    Log::info('generateMapFromSigpac: Coordenadas insertadas en PlotGeometry', [
                        'geometry_id' => $geometryId,
                    ]);

                    // Buscar o crear relación MultipartPlotSigpac
                    $mps = MultipartPlotSigpac::where('plot_id', $this->plotId)
                        ->where('sigpac_code_id', $sigpacCode->id)
                        ->first();

                    if ($mps) {
                        // Si existe, actualizar con el plot_geometry_id
                        $mps->plot_geometry_id = $geometryId;
                        $mps->updated_at = now();
                        $mps->save();
                        Log::info('generateMapFromSigpac: MultipartPlotSigpac actualizado', [
                            'mps_id' => $mps->id,
                            'geometry_id' => $geometryId,
                        ]);
                    } else {
                        // Si no existe, crear la relación
                        $mps = MultipartPlotSigpac::create([
                            'plot_id' => $this->plotId,
                            'sigpac_code_id' => $sigpacCode->id,
                            'plot_geometry_id' => $geometryId,
                        ]);
                        Log::info('generateMapFromSigpac: MultipartPlotSigpac creado', [
                            'mps_id' => $mps->id,
                            'geometry_id' => $geometryId,
                        ]);
                    }

                    $successCount++;
                    Log::info('generateMapFromSigpac: Código SIGPAC procesado exitosamente', [
                        'sigpac_code_id' => $sigpacCode->id,
                        'success_count' => $successCount,
                    ]);
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Error procesando código {$sigpacCode->code}: " . $e->getMessage();
                    Log::error('Error generating map for sigpac code', [
                        'sigpac_code_id' => $sigpacCode->id,
                        'plot_id' => $this->plotId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            DB::commit();
            Log::info('generateMapFromSigpac: Transacción confirmada', [
                'success_count' => $successCount,
                'error_count' => $errorCount,
            ]);

            // Mostrar mensaje de éxito/error
            if ($successCount > 0) {
                $message = $successCount === 1
                    ? 'Mapa generado correctamente para 1 código SIGPAC.'
                    : "Mapas generados correctamente para {$successCount} códigos SIGPAC.";
                Log::info('generateMapFromSigpac: Mostrando mensaje de éxito', ['message' => $message]);
                $this->toastSuccess($message);
            }

            if ($errorCount > 0) {
                $errorMessage = "Error al generar {$errorCount} mapa(s). " . implode(' ', array_slice($errors, 0, 3));
                Log::warning('generateMapFromSigpac: Mostrando mensaje de error', ['message' => $errorMessage]);
                $this->toastError($errorMessage);
            }

            // Recargar coordenadas si estamos editando uno específico
            if ($this->sigpacId && $successCount > 0) {
                $mps = MultipartPlotSigpac::where('plot_id', $this->plotId)
                    ->where('sigpac_code_id', $this->sigpacId)
                    ->whereNotNull('plot_geometry_id')
                    ->with('plotGeometry')
                    ->first();

                if ($mps && $mps->plotGeometry) {
                    $this->geometryId = $mps->plot_geometry_id;
                    $this->coordinates = $mps->plotGeometry->getCoordinatesAsArray();
                    Log::info('generateMapFromSigpac: Coordenadas recargadas en componente', [
                        'geometry_id' => $this->geometryId,
                        'coordinates_count' => count($this->coordinates),
                    ]);
                }
            }

            $this->dispatch('geometry-saved');
            Log::info('=== generateMapFromSigpac FINALIZADO ===');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating maps from SIGPAC', [
                'plot_id' => $this->plotId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->toastError('Error al generar los mapas. Por favor, intenta de nuevo.');
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
            ->filter(function ($plot) use ($sigpacCode) {
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
