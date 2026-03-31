<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WineryDocumentsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now  = now();
        $rows = [
            [
                'user_id'           => self::WINERY_USER_ID,
                'title'             => 'Licencia de Actividad — Elaboración de Vinos',
                'document_type'     => 'license',
                'reference_number'  => 'LA-GC-2019-004521',
                'issue_date'        => '2019-06-15',
                'expiry_date'       => '2029-06-14',
                'issuing_authority' => 'Ayuntamiento de Agaete',
                'notes'             => 'Licencia de actividad para elaboración, crianza y embotellado de vinos.',
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'user_id'           => self::WINERY_USER_ID,
                'title'             => 'Registro Sanitario de Elaborador — RESA',
                'document_type'     => 'sanitary',
                'reference_number'  => 'RGSA-35.002318/C',
                'issue_date'        => '2020-03-01',
                'expiry_date'       => null,
                'issuing_authority' => 'Agencia Española de Seguridad Alimentaria (AESAN)',
                'notes'             => 'Registro de elaborador de bebidas alcohólicas. Sin caducidad.',
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'user_id'           => self::WINERY_USER_ID,
                'title'             => 'Autorización Operador Vitivinícola — OIVE',
                'document_type'     => 'authorization',
                'reference_number'  => 'OIVE-GC-2022-00034',
                'issue_date'        => '2022-09-01',
                'expiry_date'       => '2027-08-31',
                'issuing_authority' => 'Organización Interprofesional del Vino de España',
                'notes'             => 'Autorización para declarar producción ante organismos reguladores.',
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'user_id'           => self::WINERY_USER_ID,
                'title'             => 'Certificado Agricultura Ecológica — CAAE',
                'document_type'     => 'certification',
                'reference_number'  => 'CAAE-AE-GC-00234-2021',
                'issue_date'        => '2021-01-15',
                'expiry_date'       => now()->addMonths(3)->toDateString(),
                'issuing_authority' => 'CAAE — Comité Andaluz de Agricultura Ecológica',
                'notes'             => 'Certificación para línea de vinos ecológicos. Renovación anual.',
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'user_id'           => self::WINERY_USER_ID,
                'title'             => 'Contrato de Seguro Responsabilidad Civil',
                'document_type'     => 'contract',
                'reference_number'  => 'POL-RC-2025-00567890',
                'issue_date'        => '2025-01-01',
                'expiry_date'       => '2025-12-31',
                'issuing_authority' => 'Mapfre Seguros',
                'notes'             => 'Póliza responsabilidad civil general y de producto. Cobertura 1.200.000€.',
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'user_id'           => self::WINERY_USER_ID,
                'title'             => 'Plan de Autocontrol APPCC — Bodega',
                'document_type'     => 'plan',
                'reference_number'  => 'APPCC-2024-V3',
                'issue_date'        => '2024-01-10',
                'expiry_date'       => '2026-01-09',
                'issuing_authority' => null,
                'notes'             => 'Análisis de Peligros y Puntos de Control Crítico actualizado versión 3.',
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'user_id'           => self::WINERY_USER_ID,
                'title'             => 'Declaración de Cosecha Campaña 2024',
                'document_type'     => 'declaration',
                'reference_number'  => 'DEC-COSECHA-2024-GC',
                'issue_date'        => '2024-11-30',
                'expiry_date'       => null,
                'issuing_authority' => 'Consejería de Agricultura — Gobierno de Canarias',
                'notes'             => 'Declaración anual de producción ante la Administración Autonómica.',
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'user_id'           => self::WINERY_USER_ID,
                'title'             => 'Inscripción en Denominación de Origen Gran Canaria',
                'document_type'     => 'certification',
                'reference_number'  => 'DO-GC-BODEGA-0128',
                'issue_date'        => '2020-05-20',
                'expiry_date'       => null,
                'issuing_authority' => 'Consejo Regulador D.O. Gran Canaria',
                'notes'             => 'Número de inscripción como bodega elaboradora en la D.O. Gran Canaria.',
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'user_id'           => self::WINERY_USER_ID,
                'title'             => 'Permiso Vertido Aguas Residuales',
                'document_type'     => 'permit',
                'reference_number'  => 'PV-CHSUR-2022-1245',
                'issue_date'        => '2022-04-01',
                'expiry_date'       => '2027-03-31',
                'issuing_authority' => 'Confederación Hidrográfica del Sur',
                'notes'             => 'Autorización de vertido de aguas de limpieza y proceso. Caudal máx. 50 m³/día.',
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'user_id'           => self::WINERY_USER_ID,
                'title'             => 'Contrato Gestión Residuos Sólidos (Orujos)',
                'document_type'     => 'contract',
                'reference_number'  => 'GRS-2025-00089',
                'issue_date'        => '2025-02-01',
                'expiry_date'       => '2026-01-31',
                'issuing_authority' => 'Gestora Ambiental Canarias S.L.',
                'notes'             => 'Contrato para recogida y tratamiento de orujos y lías. Frecuencia mensual.',
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
        ];

        DB::table('winery_documents')->insert($rows);
        $this->command->info('✅ Documentos de bodega: ' . count($rows) . ' registros');
    }

    private function cleanup(): void
    {
        DB::table('winery_documents')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
