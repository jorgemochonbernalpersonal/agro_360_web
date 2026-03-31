<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WineryAlertsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now  = now();
        $rows = [
            [
                'user_id'        => self::WINERY_USER_ID,
                'alert_type'     => 'maintenance',
                'severity'       => 'warning',
                'title'          => 'Mantenimiento pendiente — Depósito 003',
                'message'        => 'El depósito D003 tiene programado un sulfitado para hoy. Verificar con el enólogo.',
                'reference_type' => null,
                'reference_id'   => null,
                'is_read'        => false,
                'read_at'        => null,
                'auto_generated' => true,
                'triggered_at'   => now()->subHours(2)->toDateTimeString(),
                'expires_at'     => now()->addDays(3)->toDateTimeString(),
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'user_id'        => self::WINERY_USER_ID,
                'alert_type'     => 'expiry',
                'severity'       => 'critical',
                'title'          => 'Certificación ecológica expira en 30 días',
                'message'        => 'La certificación CAAE-AE-GC-00234-2021 vence el ' . now()->addDays(30)->format('d/m/Y') . '. Iniciar trámite de renovación.',
                'reference_type' => null,
                'reference_id'   => null,
                'is_read'        => false,
                'read_at'        => null,
                'auto_generated' => true,
                'triggered_at'   => now()->subDays(1)->toDateTimeString(),
                'expires_at'     => now()->addDays(30)->toDateTimeString(),
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'user_id'        => self::WINERY_USER_ID,
                'alert_type'     => 'fermentation',
                'severity'       => 'warning',
                'title'          => 'Temperatura de fermentación elevada',
                'message'        => 'El control del 3er día registra 22.5°C en el Tinto Joven 2025. Rango recomendado: 16-18°C. Revisar sistema de frío.',
                'reference_type' => null,
                'reference_id'   => null,
                'is_read'        => false,
                'read_at'        => null,
                'auto_generated' => true,
                'triggered_at'   => now()->subHours(6)->toDateTimeString(),
                'expires_at'     => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'user_id'        => self::WINERY_USER_ID,
                'alert_type'     => 'dispute',
                'severity'       => 'warning',
                'title'          => 'Disputa de peso en recepción pendiente de resolución',
                'message'        => 'El viticultor ha presentado una reclamación por diferencia de peso en la recepción del 3 de septiembre. Pendiente de revisión.',
                'reference_type' => null,
                'reference_id'   => null,
                'is_read'        => false,
                'read_at'        => null,
                'auto_generated' => true,
                'triggered_at'   => now()->subDays(5)->toDateTimeString(),
                'expires_at'     => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'user_id'        => self::WINERY_USER_ID,
                'alert_type'     => 'stock',
                'severity'       => 'info',
                'title'          => 'Stock bajo — Bentonita sódica',
                'message'        => 'Quedan 8 kg de bentonita sódica. Stock mínimo: 10 kg. Considerar reposición antes del próximo proceso de clarificación.',
                'reference_type' => null,
                'reference_id'   => null,
                'is_read'        => true,
                'read_at'        => now()->subDays(1)->toDateTimeString(),
                'auto_generated' => true,
                'triggered_at'   => now()->subDays(2)->toDateTimeString(),
                'expires_at'     => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'user_id'        => self::WINERY_USER_ID,
                'alert_type'     => 'label',
                'severity'       => 'info',
                'title'          => 'Lote de etiquetas casi agotado',
                'message'        => 'El lote ETQ-TINTO-2024 tiene 240 etiquetas restantes. Estimar fecha de reposición según plan de embotellado.',
                'reference_type' => null,
                'reference_id'   => null,
                'is_read'        => true,
                'read_at'        => now()->subDays(3)->toDateTimeString(),
                'auto_generated' => false,
                'triggered_at'   => now()->subDays(4)->toDateTimeString(),
                'expires_at'     => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'user_id'        => self::WINERY_USER_ID,
                'alert_type'     => 'certification',
                'severity'       => 'info',
                'title'          => 'Renovación anual de APPCC requerida',
                'message'        => 'El plan de autocontrol APPCC debe revisarse antes del 10 de enero de 2026. Contactar con el técnico de seguridad alimentaria.',
                'reference_type' => null,
                'reference_id'   => null,
                'is_read'        => false,
                'read_at'        => null,
                'auto_generated' => false,
                'triggered_at'   => now()->subDays(7)->toDateTimeString(),
                'expires_at'     => now()->addMonths(3)->toDateTimeString(),
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'user_id'        => self::WINERY_USER_ID,
                'alert_type'     => 'custom',
                'severity'       => 'info',
                'title'          => 'Visita inspector D.O. Gran Canaria programada',
                'message'        => 'Inspección anual del Consejo Regulador prevista para la semana del ' . now()->addDays(14)->format('d/m/Y') . '. Preparar documentación.',
                'reference_type' => null,
                'reference_id'   => null,
                'is_read'        => false,
                'read_at'        => null,
                'auto_generated' => false,
                'triggered_at'   => now()->subDays(2)->toDateTimeString(),
                'expires_at'     => now()->addDays(14)->toDateTimeString(),
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ];

        DB::table('winery_alerts')->insert($rows);
        $this->command->info('✅ Alertas: ' . count($rows) . ' registros (' . collect($rows)->where('is_read', false)->count() . ' sin leer)');
    }

    private function cleanup(): void
    {
        DB::table('winery_alerts')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
