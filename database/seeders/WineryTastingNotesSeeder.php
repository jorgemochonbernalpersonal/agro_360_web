<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea notas de cata profesional para los vinos.
 * Depende de: WineryWinesSeeder, WineryOenologistsSeeder
 */
class WineryTastingNotesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereNotIn('status', ['cancelled', 'in_progress'])
            ->get(['id', 'wine_type', 'vintage', 'name'])
            ->toArray();

        $oenologists = DB::table('oenologists')
            ->where('user_id', self::WINERY_USER_ID)
            ->get(['id', 'name', 'surname'])
            ->toArray();

        if (empty($wines)) {
            $this->command->warn('No hay vinos. Ejecuta WineryWinesSeeder primero.');

            return;
        }

        $aromaTintos = ['Frutos rojos maduros, cereza, ciruela, notas especiadas, vainilla, cuero suave', 'Moras, grosellas, pimienta negra, regaliz, tostados de barrica', 'Fruta roja confitada, grafito, balsámico, tabaco, cedro'];
        $aromaBlancos = ['Cítricos frescos, melocotón, flores blancas, notas minerales volcánicas', 'Manzana verde, limón, pomelo, hierba fresca, levadura', 'Tropicales (maracuyá, mango), vainilla suave, tilo'];
        $aromaRosados = ['Fresas frescas, frambuesa, flores rosadas, notas cítricas', 'Sandía, granada, rosa, ligeros tropicales'];
        $aromaEspu = ['Brioche, levadura, frutas de hueso, cítricos finos', 'Manzana verde, membrillo, bollería, burbujas finas'];
        $aromaDulces = ['Albaricoque confitado, miel de acacia, azahar, pasas sultanas, vainilla'];

        $conclusions = [
            'Vino de gran expresividad varietal con excelente relación calidad-precio. Recomendado.',
            'Equilibrado y persistente. Muestra la identidad del terruño volcánico de Gran Canaria.',
            'Complejo y elegante. Potencial de guarda de 3-5 años en botella.',
            'Fresco, vivaz y muy versátil en mesa. Ideal para gastronomía local.',
            'Expresión genuina de la variedad autóctona. Alta complejidad aromática.',
        ];

        $rows = [];
        $noteIdx = 0;

        foreach ($wines as $wine) {
            $oen = ! empty($oenologists) ? $oenologists[$noteIdx % count($oenologists)] : null;

            // Seleccionar descriptores por tipo
            $aromaPool = match ($wine->wine_type) {
                'red' => $aromaTintos,
                'white' => $aromaBlancos,
                'rose' => $aromaRosados,
                'sparkling' => $aromaEspu,
                'sweet' => $aromaDulces,
                default => $aromaBlancos,
            };
            $aromas = $aromaPool[$noteIdx % count($aromaPool)];

            // Características según tipo
            $tannins = in_array($wine->wine_type, ['red']) ? ['low', 'medium_minus', 'medium', 'medium_plus', 'high'][$noteIdx % 5] : null;
            $acidity = ['medium_minus', 'medium', 'medium_plus'][$noteIdx % 3];
            $body = match ($wine->wine_type) {
                'red' => ['medium', 'full', 'full'][$noteIdx % 3],
                'white' => ['light', 'medium'][$noteIdx % 2],
                'sparkling' => 'light',
                'sweet' => 'full',
                default => 'medium',
            };
            $finish = ['medium', 'long', 'medium', 'long', 'short'][$noteIdx % 5];

            $visualColor = match ($wine->wine_type) {
                'red' => ['Rojo cereza intenso', 'Rojo granate profundo', 'Rojo rubí brillante'][$noteIdx % 3],
                'white' => ['Amarillo pajizo con reflejos dorados', 'Amarillo verdoso brillante', 'Oro pálido'][$noteIdx % 3],
                'rose' => ['Rosa salmón intenso', 'Rosa frambuesa brillante'][$noteIdx % 2],
                'sparkling' => ['Amarillo pálido con finas burbujas persistentes', 'Rosa salmón espumoso'][$noteIdx % 2],
                'sweet' => 'Ámbar dorado intenso',
                default => 'Amarillo verdoso',
            };

            $score = round(82 + ($noteIdx % 12) + mt_rand(0, 3), 1);
            $evaluationDate = now()->subDays(mt_rand(10, 180))->format('Y-m-d');

            $rows[] = [
                'user_id' => self::WINERY_USER_ID,
                'wine_id' => $wine->id,
                'oenologist_id' => $oen ? $oen->id : null,
                'evaluation_date' => $evaluationDate,
                'evaluator_name' => $oen ? ($oen->name.' '.($oen->surname ?? '')) : 'Equipo de cata Bodega Agaete',
                'visual_color' => $visualColor,
                'visual_clarity' => ['brilliant', 'clear', 'brilliant'][$noteIdx % 3],
                'visual_intensity' => match ($wine->wine_type) {
                    'red' => ['medium', 'deep', 'very_deep'][$noteIdx % 3],
                    'sweet' => 'deep',
                    default => ['pale', 'medium'][$noteIdx % 2],
                },
                'aroma_intensity' => ['medium', 'pronounced', 'complex', 'pronounced'][$noteIdx % 4],
                'aroma_descriptors' => $aromas,
                'palate_acidity' => $acidity,
                'palate_tannins' => $tannins,
                'palate_body' => $body,
                'palate_finish' => $finish,
                'overall_score' => $score,
                'overall_conclusion' => $conclusions[$noteIdx % count($conclusions)],
                'notes' => null,
                'created_by' => self::WINERY_USER_ID,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $noteIdx++;
        }

        DB::table('wine_tasting_notes')->insert($rows);

        $this->command->info('✅ Notas de cata: '.count($rows).' registros');
    }

    private function cleanup(): void
    {
        $wineIds = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id');

        DB::table('wine_tasting_notes')->whereIn('wine_id', $wineIds)->delete();
    }
}
