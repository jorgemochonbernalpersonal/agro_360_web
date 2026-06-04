<?php

namespace App\Livewire\Concerns;

use App\Models\Plot;
use App\Services\CatastroGeometryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Genera la geometría de una parcela desde el Catastro a partir de su
 * referencia catastral (plots.code_parcel). Alternativa a SIGPAC: no requiere
 * códigos SIGPAC asociados.
 *
 * El componente que lo use debe disponer de los toasts de WithToastNotifications.
 * En componentes con una sola parcela ($this->plot), $plotId puede omitirse.
 */
trait GeneratesCatastroGeometry
{
    public function generateMapFromCatastro($plotId = null): void
    {
        $plotId = $plotId ?? ($this->plot->id ?? null);

        if (! $plotId) {
            $this->toastError(__('Parcela no encontrada.'));

            return;
        }

        $plot = Plot::findOrFail($plotId);

        if (! Auth::user()->can('update', $plot)) {
            $this->toastError(__('No tienes permiso para modificar esta parcela.'));

            return;
        }

        if (! $plot->code_parcel) {
            $this->toastError(__('Esta parcela no tiene referencia catastral.'));

            return;
        }

        $service = app(CatastroGeometryService::class);

        try {
            DB::beginTransaction();

            $wkt = $service->fetchWkt($plot->code_parcel);

            if (! $wkt) {
                DB::rollBack();
                $this->toastError(__('No se pudo obtener la geometría del catastro para la referencia :ref.', ['ref' => $plot->code_parcel]));

                return;
            }

            if (! preg_match('/^(POLYGON|MULTIPOLYGON)\s*\(.+\)$/i', $wkt)) {
                DB::rollBack();
                $this->toastError(__('El catastro devolvió una geometría con formato inválido.'));

                return;
            }

            $service->upsertGeometry($plot->id, $wkt);

            DB::commit();

            $this->toastSuccess(__('Geometría generada correctamente desde el catastro.'));

            // Recargar relaciones de geometría para que render() calcule
            // $hasGeometry/$geometrySource/$plotGeometries sin queries extra.
            if (isset($this->plot)) {
                $this->plot->load([
                    'multiplePlotSigpacs.sigpacCode',
                    'multiplePlotSigpacs.plotGeometry',
                ]);
            }

            $this->dispatch('geometry-saved');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating map from Catastro', [
                'plot_id' => $plot->id,
                'reference' => $plot->code_parcel,
                'error' => $e->getMessage(),
            ]);
            $this->toastError(__('Error al generar la geometría desde el catastro. Por favor, intenta de nuevo.'));
        }
    }
}
