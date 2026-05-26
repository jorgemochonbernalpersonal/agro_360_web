<?php

namespace App\Livewire\Viticulturist\Pac\EcoSchemes;

use App\Models\AgriculturalActivity;
use App\Models\PacDeclaration;
use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $yearFilter = '';

    public function mount(): void
    {
        $this->yearFilter = (string) now()->year;
    }

    public function render()
    {
        $viticulturistId = Auth::id();
        $year            = (int) ($this->yearFilter ?: now()->year);

        // Declaración PAC del año seleccionado
        $declaration = PacDeclaration::forViticulturist($viticulturistId)
            ->where('year', $year)
            ->with('items.plot')
            ->first();

        // Parcelas del viticulturist
        $plots = Plot::where('viticulturist_id', $viticulturistId)
            ->where('active', true)
            ->get()
            ->keyBy('id');

        // Actividades del año en el cuaderno
        $activities = AgriculturalActivity::forUser($viticulturistId)
            ->with(['culturalWork', 'phytosanitaryTreatment', 'plot'])
            ->whereYear('activity_date', $year)
            ->get();

        // Eco-regímenes declarados (union de todos los items de la declaración)
        $declaredSchemes = [];
        if ($declaration) {
            foreach ($declaration->items as $item) {
                foreach ($item->eco_schemes ?? [] as $scheme) {
                    $declaredSchemes[$scheme] = true;
                }
            }
        }

        // Evidencias por eco-régimen
        $evidence = $this->buildEvidence($activities, $plots, $year);

        // Años con declaraciones disponibles
        $availableYears = PacDeclaration::forViticulturist($viticulturistId)
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (!in_array(now()->year, $availableYears)) {
            array_unshift($availableYears, now()->year);
        }

        return view('livewire.viticulturist.pac.eco-schemes.index', [
            'declaration'    => $declaration,
            'ecoSchemes'     => PacDeclaration::ECO_SCHEMES,
            'declaredSchemes'=> $declaredSchemes,
            'evidence'       => $evidence,
            'availableYears' => $availableYears,
            'year'           => $year,
        ])->layout('layouts.app');
    }

    private function buildEvidence($activities, $plots, int $year): array
    {
        $evidence = [];

        // cover_crops — labores culturales tipo cubierta/acolchado/siembra
        $coverKeywords = ['cubierta', 'acolchado', 'siembra', 'cobertura', 'grass', 'herbac'];
        $coverCount = $activities->filter(function ($a) use ($coverKeywords) {
            if ($a->activity_type !== 'cultural' || !$a->culturalWork) return false;
            $type = strtolower($a->culturalWork->work_type ?? '');
            foreach ($coverKeywords as $kw) {
                if (str_contains($type, $kw)) return true;
            }
            return false;
        })->count();
        $evidence['cover_crops'] = [
            'count'   => $coverCount,
            'status'  => $coverCount > 0 ? 'ok' : 'missing',
            'detail'  => $coverCount > 0
                ? "{$coverCount} labor(es) de cubierta vegetal registrada(s)"
                : 'No se encontraron labores de cubierta vegetal en el cuaderno',
        ];

        // no_tillage — ausencia de labores de laboreo intensivo
        $tillageKeywords = ['laboreo', 'arado', 'subsolado', 'cultivador'];
        $tillageCount = $activities->filter(function ($a) use ($tillageKeywords) {
            if ($a->activity_type !== 'cultural' || !$a->culturalWork) return false;
            $type = strtolower($a->culturalWork->work_type ?? '');
            foreach ($tillageKeywords as $kw) {
                if (str_contains($type, $kw)) return true;
            }
            return false;
        })->count();
        $evidence['no_tillage'] = [
            'count'   => $tillageCount,
            'status'  => $tillageCount === 0 ? 'ok' : 'warning',
            'detail'  => $tillageCount === 0
                ? 'No se registraron labores de laboreo intensivo'
                : "{$tillageCount} labor(es) de laboreo encontrada(s) — revisar compatibilidad con el eco-régimen",
        ];

        // organic — parcelas con is_organic = true
        $organicPlots = $plots->where('is_organic', true)->count();
        $evidence['organic'] = [
            'count'   => $organicPlots,
            'status'  => $organicPlots > 0 ? 'ok' : 'missing',
            'detail'  => $organicPlots > 0
                ? "{$organicPlots} parcela(s) marcada(s) como producción ecológica"
                : 'Ninguna parcela tiene la bandera de producción ecológica activada',
        ];

        // reduced_phyto — pocos tratamientos fitosanitarios
        $phytoCount = $activities->where('activity_type', 'phytosanitary')->count();
        $phytoLimit = max(1, $plots->count() * 4); // heurístico: 4 tratamientos/año/parcela
        $evidence['reduced_phyto'] = [
            'count'   => $phytoCount,
            'status'  => $phytoCount <= $phytoLimit ? 'ok' : 'warning',
            'detail'  => "{$phytoCount} tratamiento(s) fitosanitario(s) registrado(s) en {$year}",
        ];

        // integrated_pest_mgmt — mezcla de labores + observaciones
        $obsCount = $activities->where('activity_type', 'observation')->count();
        $evidence['integrated_pest_mgmt'] = [
            'count'   => $obsCount,
            'status'  => $obsCount >= 2 ? 'ok' : ($obsCount > 0 ? 'warning' : 'missing'),
            'detail'  => $obsCount > 0
                ? "{$obsCount} observación(es) / seguimiento registrado(s)"
                : 'Sin observaciones de seguimiento de plagas en el cuaderno',
        ];

        // biodiversity — difícil de verificar desde cuaderno, marcamos como informativo
        $evidence['biodiversity'] = [
            'count'   => null,
            'status'  => 'info',
            'detail'  => __('No verificable automáticamente — requiere inspección en campo'),
        ];

        // precision_fertilization — fertilizaciones registradas
        $fertCount = $activities->where('activity_type', 'fertilization')->count();
        $evidence['precision_fertilization'] = [
            'count'   => $fertCount,
            'status'  => $fertCount > 0 ? 'ok' : 'missing',
            'detail'  => $fertCount > 0
                ? "{$fertCount} fertilización(es) registrada(s) con datos de dosis"
                : 'Sin fertilizaciones registradas en el cuaderno para este año',
        ];

        return $evidence;
    }
}
