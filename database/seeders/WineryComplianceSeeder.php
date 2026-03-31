<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea registros de cumplimiento regulatorio:
 * - Ecocertificaciones
 * - Registros sanitarios
 * - Autorizaciones de embotellado
 * Depende de: WineryWinesSeeder
 */
class WineryComplianceSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanupAll();
        $now = now();

        $this->seedEcoCertifications($now);
        $this->seedSanitaryRegistrations($now);
        $this->seedBottlingAuthorizations($now);
    }

    private function seedEcoCertifications(\Carbon\Carbon $now): void
    {
        $certs = [
            [
                'name'              => 'Certificación Agricultura Ecológica — Viñas',
                'certification_type'=> 'organic',
                'certifying_body'   => 'CAAE — Comité Andaluz de Agricultura Ecológica',
                'certificate_number'=> 'CAAE-CAE-GC-2023-004821',
                'valid_from'        => '2023-01-01',
                'valid_until'       => '2026-12-31',
                'status'            => 'active',
                'notes'             => 'Certificación para parcelas ecológicas en Agaete y Artenara. Extensión total: 12,4 ha.',
            ],
            [
                'name'              => 'Certificación Biodinámica — Viñedos en Ladera',
                'certification_type'=> 'biodynamic',
                'certifying_body'   => 'Demeter International',
                'certificate_number'=> 'DEMETER-ES-2024-00312',
                'valid_from'        => '2024-04-01',
                'valid_until'       => '2025-03-31',
                'status'            => 'active',
                'notes'             => 'En proceso de renovación. Parcelas en conversión desde 2021.',
            ],
            [
                'name'              => 'Certificación Vegan-Friendly',
                'certification_type'=> 'vegan',
                'certifying_body'   => 'The Vegan Society',
                'certificate_number'=> 'TVS-ES-2024-8821',
                'valid_from'        => '2024-01-01',
                'valid_until'       => '2024-12-31',
                'status'            => 'expired',
                'notes'             => 'Pendiente de renovación para campaña 2025. Se ha evitado uso de colas y gelatinas animales.',
            ],
            [
                'name'              => 'Sello de Calidad D.O. Gran Canaria',
                'certification_type'=> 'denomination',
                'certifying_body'   => 'Consejo Regulador D.O. Gran Canaria',
                'certificate_number'=> 'DO-GC-BOD-2022-00089',
                'valid_from'        => '2022-09-01',
                'valid_until'       => '2027-08-31',
                'status'            => 'active',
                'notes'             => 'Habilitación para producir y comercializar vinos amparados bajo D.O. Gran Canaria.',
            ],
        ];

        $rows = array_map(fn($c) => array_merge($c, [
            'user_id'    => self::WINERY_USER_ID,
            'created_at' => $now,
            'updated_at' => $now,
        ]), $certs);

        DB::table('eco_certifications')->insert($rows);
        $this->command->info('✅ Ecocertificaciones: ' . count($rows) . ' registros');
    }

    private function seedSanitaryRegistrations(\Carbon\Carbon $now): void
    {
        $regs = [
            [
                'registration_number' => 'RGSA 35.002318/C',
                'registration_type'   => 'elaborador',
                'activity_description'=> 'Elaboración, crianza y embotellado de vinos tranquilos y espumosos',
                'registration_date'   => '2018-03-15',
                'renewal_date'        => '2028-03-15',
                'issuing_authority'   => 'Consejería de Sanidad del Gobierno de Canarias',
                'status'              => 'active',
                'notes'               => 'Registro General Sanitario de Empresas Alimentarias. Habilitación para elaboración y embotellado.',
            ],
            [
                'registration_number' => 'RAE-GC-2019-00156',
                'registration_type'   => 'almacenista',
                'activity_description'=> 'Almacenamiento y comercialización de vinos a granel y embotellados',
                'registration_date'   => '2019-06-01',
                'renewal_date'        => '2025-06-01',
                'issuing_authority'   => 'Agencia Canaria de la Calidad Agroalimentaria (ACCA)',
                'status'              => 'active',
                'notes'               => 'Pendiente renovación en junio 2025. Expediente iniciado.',
            ],
            [
                'registration_number' => 'OIVE-GC-2022-00034',
                'registration_type'   => 'operador',
                'activity_description'=> 'Operador vitivinícola inscrito en el Registro de la Viña y el Vino — OIVE',
                'registration_date'   => '2022-01-10',
                'renewal_date'        => null,
                'issuing_authority'   => 'Fondo Español de Garantía Agraria (FEGA)',
                'status'              => 'active',
                'notes'               => 'Habilitación para declaraciones INFOVI/SILICIE. Sin caducidad.',
            ],
        ];

        $rows = array_map(fn($r) => array_merge($r, [
            'user_id'    => self::WINERY_USER_ID,
            'created_at' => $now,
            'updated_at' => $now,
        ]), $regs);

        DB::table('sanitary_registrations')->insert($rows);
        $this->command->info('✅ Registros sanitarios: ' . count($rows) . ' registros');
    }

    private function seedBottlingAuthorizations(\Carbon\Carbon $now): void
    {
        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereIn('status', ['bottled', 'aged'])
            ->limit(5)
            ->get(['id', 'name', 'vintage', 'volume_liters'])
            ->toArray();

        $rows = [];
        foreach ($wines as $idx => $wine) {
            $authorizedVol = round(($wine->volume_liters ?? 5000) * 1.05, 3); // 5% margen
            $validFrom     = now()->subMonths(8 - $idx)->format('Y-m-d');
            $validUntil    = now()->addMonths(4 + $idx)->format('Y-m-d');

            $rows[] = [
                'user_id'                => self::WINERY_USER_ID,
                'authorization_number'   => 'AE-GC-' . $wine->vintage . '-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                'authorization_type'     => 'embotellado',
                'wine_id'                => $wine->id,
                'authorized_volume_liters'=> $authorizedVol,
                'valid_from'             => $validFrom,
                'valid_until'            => $validUntil,
                'issuing_authority'      => 'Consejo Regulador D.O. Gran Canaria',
                'status'                 => now()->parse($validUntil)->isFuture() ? 'active' : 'expired',
                'conditions'             => 'Embotellado en instalaciones propias. Formato autorizado: 375ml, 750ml, 1500ml.',
                'notes'                  => null,
                'created_at'             => $now,
                'updated_at'             => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('bottling_authorizations')->insert($rows);
        }
        $this->command->info('✅ Autorizaciones de embotellado: ' . count($rows) . ' registros');
    }

    private function cleanupAll(): void
    {
        DB::table('eco_certifications')->where('user_id', self::WINERY_USER_ID)->delete();
        DB::table('sanitary_registrations')->where('user_id', self::WINERY_USER_ID)->delete();
        DB::table('bottling_authorizations')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
