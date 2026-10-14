<?php

namespace App\Observers;

use App\Jobs\GeocodePlotJob;
use App\Models\Municipality;
use App\Models\OnboardingProgress;
use App\Models\Plot;
use Illuminate\Support\Facades\Cache;

class PlotObserver
{
    /**
     * Handle the Plot "created" event.
     */
    public function created(Plot $plot): void
    {
        // Marcar paso de onboarding como completado
        if ($plot->viticulturist_id) {
            $progress = OnboardingProgress::getOrCreate(
                $plot->viticulturist_id,
                OnboardingProgress::STEP_CREATE_PLOT
            );

            if (! $progress->isCompleted()) {
                $progress->markAsCompleted();
                Cache::forget("nav_onboarding_pending_{$plot->viticulturist_id}");
            }
        }

        $this->geocodeMunicipalityIfNeeded($plot->municipality_id);
    }

    /**
     * Handle the Plot "updated" event.
     */
    public function updated(Plot $plot): void
    {
        // Si cambia el municipio, geocodifica el nuevo si no tiene coordenadas
        if ($plot->wasChanged('municipality_id')) {
            $this->geocodeMunicipalityIfNeeded($plot->municipality_id);
        }
    }

    /**
     * Handle the Plot "deleted" event.
     */
    public function deleted(Plot $plot): void
    {
        //
    }

    /**
     * Handle the Plot "restored" event.
     */
    public function restored(Plot $plot): void
    {
        //
    }

    /**
     * Handle the Plot "force deleted" event.
     */
    public function forceDeleted(Plot $plot): void
    {
        //
    }

    private function geocodeMunicipalityIfNeeded(?int $municipalityId): void
    {
        if (! $municipalityId) {
            return;
        }

        // Comprobación barata (sin red) antes de encolar: evita despachar un
        // job que no haría nada si el municipio ya tiene coordenadas.
        $municipality = Municipality::find($municipalityId);

        if (! $municipality || $municipality->lat !== null) {
            return;
        }

        // La llamada a Nominatim se hace en background (antes: síncrona dentro
        // de la request de guardado de la parcela, con timeout por defecto de
        // 30s). afterCommit() para no despachar si la transacción de guardado
        // de la parcela termina haciendo rollback.
        GeocodePlotJob::dispatch($municipalityId)->afterCommit();
    }
}
