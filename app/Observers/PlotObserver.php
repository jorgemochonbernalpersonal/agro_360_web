<?php

namespace App\Observers;

use App\Models\Municipality;
use App\Models\Plot;
use App\Models\OnboardingProgress;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

            if (!$progress->isCompleted()) {
                $progress->markAsCompleted();
                Cache::forget("nav_onboarding_pending_{$plot->viticulturist_id}");
            }
        }

        // Invalidar caché de navegación para que el sidebar muestre las secciones avanzadas
        if ($plot->viticulturist_id) {
            Cache::forget("nav_has_plots_{$plot->viticulturist_id}");
        }

        $this->geocodeMunicipalityIfNeeded($plot->municipality_id);
    }

    private function geocodeMunicipalityIfNeeded(?int $municipalityId): void
    {
        if (! $municipalityId) return;

        $municipality = Municipality::with('province')->find($municipalityId);

        if (! $municipality || $municipality->lat !== null) return;

        try {
            $name     = trim(explode('/', $municipality->name)[0]);
            $province = trim(explode('/', $municipality->province?->name ?? '')[0]);

            $http = Http::withHeaders(['User-Agent' => 'Agro365/1.0 (info@agro365.es)']);

            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }

            $results = $http->get('https://nominatim.openstreetmap.org/search', [
                'city'    => $name,
                'state'   => $province,
                'country' => 'Spain',
                'format'  => 'json',
                'limit'   => 1,
            ])->json();

            if (!empty($results)) {
                $municipality->update([
                    'lat' => round((float) $results[0]['lat'], 6),
                    'lng' => round((float) $results[0]['lon'], 6),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("No se pudo geocodificar municipio {$municipalityId}: {$e->getMessage()}");
        }
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
        if ($plot->viticulturist_id) {
            Cache::forget("nav_has_plots_{$plot->viticulturist_id}");
        }
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
}
