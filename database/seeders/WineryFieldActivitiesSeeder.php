<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera actividades de campo (agricultural_activities) para los viticultores
 * vinculados a la bodega (user_id=1).
 *
 * Crea ~60-80 actividades distribuidas entre los 6 viticultores demo,
 * cubriendo los 9 tipos de actividad del cuaderno de campo, con fechas
 * coherentes con el ciclo vegetativo de la vid (2023-2025).
 *
 * Depende de: WineryGrapeReceptionsSeeder (viticultores, plots, plantings, campañas).
 */
class WineryFieldActivitiesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    // Ciclo vegetativo: actividades típicas por mes (hemisferio norte, Canarias)
    // Mes => [tipos de actividad posibles]
    private const SEASONAL_ACTIVITIES = [
        1  => ['pruning', 'cultural', 'observation'],                          // Enero: poda invernal
        2  => ['pruning', 'cultural', 'fertilization', 'observation'],         // Febrero: poda + abonado fondo
        3  => ['fertilization', 'phytosanitary', 'cultural', 'phenology'],     // Marzo: brotación
        4  => ['phytosanitary', 'irrigation', 'fertilization', 'phenology'],   // Abril: crecimiento vegetativo
        5  => ['phytosanitary', 'irrigation', 'observation', 'phenology'],     // Mayo: floración
        6  => ['phytosanitary', 'irrigation', 'cultural', 'observation'],      // Junio: cuajado
        7  => ['irrigation', 'phytosanitary', 'observation', 'phenology'],     // Julio: envero
        8  => ['harvest', 'observation', 'irrigation', 'phenology'],           // Agosto: inicio vendimia
        9  => ['harvest', 'observation', 'phytosanitary'],                     // Septiembre: vendimia plena
        10 => ['harvest', 'post_harvest', 'observation'],                      // Octubre: fin vendimia + post
        11 => ['post_harvest', 'fertilization', 'cultural'],                   // Noviembre: post-vendimia
        12 => ['cultural', 'observation', 'pruning'],                          // Diciembre: prepoda
    ];

    private const WEATHER_CONDITIONS = [
        'soleado', 'parcialmente nublado', 'nublado', 'lluvia ligera',
        'viento moderado', 'calima', 'despejado', 'bruma matinal',
    ];

    private const PHENOLOGICAL_STAGES = [
        'pruning'       => ['reposo_invernal', 'lloro'],
        'cultural'      => ['reposo_invernal', 'brotacion', 'desarrollo_vegetativo', 'envero'],
        'fertilization' => ['brotacion', 'floracion', 'cuajado', 'envero'],
        'phytosanitary' => ['brotacion', 'floracion', 'cuajado', 'envero', 'maduracion'],
        'irrigation'    => ['brotacion', 'floracion', 'cuajado', 'envero', 'maduracion'],
        'observation'   => ['brotacion', 'floracion', 'cuajado', 'envero', 'maduracion', 'vendimia'],
        'harvest'       => ['maduracion', 'vendimia'],
        'post_harvest'  => ['post_vendimia'],
        'phenology'     => ['brotacion', 'floracion', 'cuajado', 'envero', 'maduracion'],
    ];

    private const ACTIVITY_NOTES = [
        'pruning'       => [
            'Poda en vaso. Se eliminan sarmientos débiles y mal orientados.',
            'Poda de formación en espaldera. Dejar 6-8 yemas por pulgar.',
            'Prepoda mecánica + repaso manual. Buenas condiciones de corte.',
            'Poda invernal completa. Madera sana, sin síntomas de enfermedades.',
        ],
        'cultural'      => [
            'Laboreo superficial entre calles. Suelo compactado por lluvias.',
            'Desniete y aclareo de pámpanos. Buen vigor vegetativo.',
            'Deshojado zona de racimos. Mejorar aireación y exposición solar.',
            'Atado de sarmientos a la espaldera. Guiado vertical.',
            'Despunte de brotes en exceso. Control del vigor.',
        ],
        'fertilization' => [
            'Abonado de fondo con compost de orujo. 2 t/ha.',
            'Fertirrigación con N-P-K 12-8-16. Dosis según análisis foliar.',
            'Aplicación de ácidos húmicos vía riego. Mejora estructura del suelo.',
            'Abonado potásico pre-envero. Favorecer maduración.',
        ],
        'phytosanitary' => [
            'Tratamiento preventivo azufre en polvo contra oídio. 25 kg/ha.',
            'Aplicación caldo bordelés contra mildiu. Condiciones de riesgo.',
            'Tratamiento con Bacillus thuringiensis contra polilla del racimo.',
            'Aplicación de azufre mojable + cobre. Tratamiento combinado.',
            'Confusión sexual con difusores para polilla del racimo.',
        ],
        'irrigation'    => [
            'Riego por goteo. 2h a 4 L/h. Suelo con déficit hídrico notable.',
            'Riego de apoyo post-cuajado. Control de estrés hídrico moderado.',
            'Riego deficitario controlado. Restricción 50% respecto a ETc.',
            'Fertirriego con microelementos (Fe, Mn, Zn). Corrección clorosis.',
        ],
        'observation'   => [
            'Inspección visual de parcela. Sin incidencias fitosanitarias.',
            'Observación de síntomas de estrés hídrico en hojas basales.',
            'Control de maduración: muestreo de bayas para análisis.',
            'Detección de focos de botrytis en racimos de zona baja.',
            'Revisión de trampas cromáticas. Nivel de capturas bajo.',
        ],
        'harvest'       => [
            'Vendimia manual selectiva. Selección en campo por sanidad.',
            'Cosecha mecánica parcial. Primera pasada zona alta de la parcela.',
            'Vendimia nocturna. Temperatura de uva al recogido: 18°C.',
        ],
        'post_harvest'  => [
            'Aplicación de abono orgánico post-vendimia. Restitución de reservas.',
            'Tratamiento fungicida post-cosecha contra yesca y eutipiosis.',
            'Riego post-vendimia para mantener actividad foliar.',
        ],
        'phenology'     => [
            'Registro fenológico: estado BBCH 09 — punta verde visible.',
            'Observación de floración. Cuajado estimado al 60%.',
            'Envero iniciado. 50% de bayas cambiando color.',
            'Control de madurez: 12.5° Baumé, pH 3.35, acidez total 6.2 g/L.',
        ],
    ];

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        // Obtener viticultores demo vinculados con notebook_access
        $viticulturistIds = DB::table('winery_viticulturist')
            ->where('winery_id', self::WINERY_USER_ID)
            ->where('notebook_access', true)
            ->pluck('viticulturist_id')
            ->toArray();

        if (empty($viticulturistIds)) {
            $this->command->warn('  ⚠️  Sin viticultores con acceso al cuaderno. Saltando actividades de campo.');
            return;
        }

        // Obtener plots y plantings por viticultor
        $plotsByVit = DB::table('plots')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->where('active', true)
            ->get()
            ->groupBy('viticulturist_id');

        $plantingsByPlot = DB::table('plot_plantings')
            ->whereIn('plot_id', $plotsByVit->flatten()->pluck('id'))
            ->get()
            ->groupBy('plot_id');

        // Obtener campañas por viticultor
        $campaignsByVit = DB::table('campaigns')
            ->whereIn('viticulturist_id', $viticulturistIds)
            ->get()
            ->groupBy('viticulturist_id');

        $rows = [];
        $idx  = 0;

        foreach ($viticulturistIds as $vitId) {
            $plots     = $plotsByVit->get($vitId, collect());
            $campaigns = $campaignsByVit->get($vitId, collect());

            if ($plots->isEmpty() || $campaigns->isEmpty()) continue;

            // Generate activities for each vintage year (2023, 2024, 2025)
            foreach ([2023, 2024, 2025] as $vintage) {
                $campaign = $campaigns->firstWhere('year', $vintage);
                if (!$campaign) continue;

                // Determine months to generate (full year for past, up to current month for 2025)
                $months = $vintage < 2025
                    ? range(1, 12)
                    : range(1, min(9, (int) now()->format('n'))); // Up to Sep 2025 or current

                // ~3-5 activities per viticulturist per vintage
                $activitiesPerVintage = mt_rand(3, 5);
                $selectedMonths = (array) array_rand(array_flip($months), min($activitiesPerVintage, count($months)));
                sort($selectedMonths);

                foreach ($selectedMonths as $month) {
                    $possibleTypes = self::SEASONAL_ACTIVITIES[$month];
                    $activityType  = $possibleTypes[array_rand($possibleTypes)];

                    // Pick a random plot and planting
                    $plot     = $plots->random();
                    $plotPlantings = $plantingsByPlot->get($plot->id, collect());
                    $planting = $plotPlantings->isNotEmpty() ? $plotPlantings->random() : null;

                    $day  = mt_rand(1, 28); // Safe for all months
                    $date = sprintf('%04d-%02d-%02d', $vintage, $month, $day);

                    $notes      = $this->pickNote($activityType, $idx);
                    $weather    = self::WEATHER_CONDITIONS[$idx % count(self::WEATHER_CONDITIONS)];
                    $stages     = self::PHENOLOGICAL_STAGES[$activityType] ?? [];
                    $stage      = !empty($stages) ? $stages[array_rand($stages)] : null;

                    // Temperature coherent with month (Canarias: mild year-round)
                    $baseTemp = match (true) {
                        $month <= 2 || $month >= 11 => mt_rand(140, 200) / 10,  // 14-20°C
                        $month >= 6 && $month <= 9  => mt_rand(240, 340) / 10,  // 24-34°C
                        default                     => mt_rand(180, 260) / 10,  // 18-26°C
                    };

                    $rows[] = [
                        'plot_id'            => $plot->id,
                        'plot_planting_id'   => $planting?->id,
                        'viticulturist_id'   => $vitId,
                        'campaign_id'        => $campaign->id,
                        'activity_type'      => $activityType,
                        'phenological_stage' => $stage,
                        'activity_date'      => $date,
                        'weather_conditions' => $weather,
                        'temperature'        => $baseTemp,
                        'notes'              => $notes,
                        'is_locked'          => $vintage < 2025,
                        'locked_at'          => $vintage < 2025 ? $now : null,
                        'locked_by'          => $vintage < 2025 ? $vitId : null,
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ];

                    $idx++;
                }
            }
        }

        if (empty($rows)) {
            $this->command->warn('  ⚠️  No se generaron actividades de campo.');
            return;
        }

        // Shuffle to avoid monotonous ordering by viticulturist
        shuffle($rows);

        DB::table('agricultural_activities')->insert($rows);

        // Count by type for summary
        $byType = collect($rows)->groupBy('activity_type')->map->count()->sortDesc();
        $typesSummary = $byType->map(fn($count, $type) => "{$type}={$count}")->implode(', ');

        $this->command->info("✅ Actividades de campo: " . count($rows) . " registros ({$typesSummary})");
    }

    private function pickNote(string $type, int $idx): string
    {
        $notes = self::ACTIVITY_NOTES[$type] ?? ['Actividad registrada.'];
        return $notes[$idx % count($notes)];
    }

    private function cleanup(): void
    {
        $viticulturistIds = DB::table('winery_viticulturist')
            ->where('winery_id', self::WINERY_USER_ID)
            ->pluck('viticulturist_id')
            ->toArray();

        if (!empty($viticulturistIds)) {
            DB::table('agricultural_activities')
                ->whereIn('viticulturist_id', $viticulturistIds)
                ->delete();
        }
    }
}
