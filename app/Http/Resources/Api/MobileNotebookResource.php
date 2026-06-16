<?php

namespace App\Http\Resources\Api;

use App\Models\PostHarvestTreatment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Flat-format resource for the mobile notebook list endpoints.
 * Returns the exact field structure expected by the Kotlin/KMP DTOs.
 */
class MobileNotebookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return match ($this->activity_type) {
            'phytosanitary' => $this->treatmentArray(),
            'fertilization' => $this->fertilizationArray(),
            'irrigation' => $this->irrigationArray(),
            'observation' => $this->observationArray(),
            'harvest' => $this->harvestArray(),
            'cultural' => $this->culturalWorkArray(),
            'pruning' => $this->pruningArray(),
            'post_harvest' => $this->postHarvestArray(),
            default => $this->base(),
        };
    }

    // ── Common base fields ────────────────────────────────────────────────────

    private function base(): array
    {
        return [
            'id' => $this->id,
            'plot_id' => $this->plot_id,
            'plot_name' => $this->plot?->name,
            'date' => $this->activity_date?->toDateString(),
            'notes' => $this->notes,
        ];
    }

    // ── Per-type flat arrays ──────────────────────────────────────────────────

    private function treatmentArray(): array
    {
        $t = $this->phytosanitaryTreatment;

        return array_merge($this->base(), [
            'product' => $t?->product->name ?? '',
            'dose' => (string) ($t->dose_per_hectare ?? '0'),
            'dose_unit' => 'kg/ha',
            'pest' => $t?->target_pest,
            'application_method' => $t?->application_method,
            'safety_period_days' => $t?->reentry_period_days,
        ]);
    }

    private function irrigationArray(): array
    {
        $i = $this->irrigation;

        return array_merge($this->base(), [
            'duration_minutes' => $i->duration_minutes ?? 0,
            'water_volume' => $i?->water_volume !== null ? (float) $i->water_volume : null,
            'water_volume_unit' => $i?->water_volume_unit,
            'irrigation_type' => $i?->irrigation_method,
            'is_fertirrigation' => (bool) ($i->is_fertirrigation ?? false),
        ]);
    }

    private function observationArray(): array
    {
        $o = $this->observation;

        return array_merge($this->base(), [
            'type' => $o?->observation_type,
            'description' => $o->description ?? '',
            'affected_area_percentage' => $o?->affected_area_percentage !== null
                ? (float) $o->affected_area_percentage : null,
            'threshold_exceeded' => (bool) ($o->threshold_exceeded ?? false),
            'follow_up_date' => $o?->follow_up_date?->toDateString(),
        ]);
    }

    private function harvestArray(): array
    {
        $h = $this->harvest;

        return array_merge($this->base(), [
            'variety' => $this->plotPlanting?->grapeVariety?->name,
            'weight_kg' => $h?->total_weight !== null ? (float) $h->total_weight : 0.0,
            'brix_degrees' => $h?->brix_degree !== null ? (float) $h->brix_degree : null,
        ]);
    }

    private function culturalWorkArray(): array
    {
        $c = $this->culturalWork;

        return array_merge($this->base(), [
            'work_type' => $c?->work_type,
            'machinery' => $c?->description,
            'residue_management' => $c?->residue_management,
        ]);
    }

    private function pruningArray(): array
    {
        $c = $this->culturalWork;

        return array_merge($this->base(), [
            'pruning_type' => $c?->pruning_type,
            'buds_per_vine' => $c?->productive_buds_per_hectare,
            'pruning_weight_kg' => null,
        ]);
    }

    private function fertilizationArray(): array
    {
        $f = $this->fertilization;

        return array_merge($this->base(), [
            'fertilizer_type' => $f?->fertilizer_type,
            'fertilizer_name' => $f?->fertilizer_name,
            'quantity' => $f?->quantity !== null ? (float) $f->quantity : null,
            'application_method' => $f?->application_method,
            'area_applied' => $f?->area_applied !== null ? (float) $f->area_applied : null,
            'nitrogen_uf' => $f?->nitrogen_uf !== null ? (float) $f->nitrogen_uf : null,
            'phosphorus_uf' => $f?->phosphorus_uf !== null ? (float) $f->phosphorus_uf : null,
            'potassium_uf' => $f?->potassium_uf !== null ? (float) $f->potassium_uf : null,
        ]);
    }

    private function postHarvestArray(): array
    {
        $p = $this->postHarvestTreatment;
        $productName = $p?->product->name
            ?? (isset($p->application_type)
                ? (PostHarvestTreatment::APPLICATION_TYPES[$p->application_type] ?? '')
                : '');

        return array_merge($this->base(), [
            'product' => $productName,
            'dose' => (string) ($p->dose_per_hectare ?? '0'),
            'dose_unit' => $p?->dose_unit,
            'application_method' => $p?->application_type,
            'reentry_interval_hours' => $p?->reentry_interval_hours,
        ]);
    }
}
