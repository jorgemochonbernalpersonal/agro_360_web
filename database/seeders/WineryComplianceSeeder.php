<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera 450 registros por cada tabla de cumplimiento regulatorio:
 *   · eco_certifications        (450)
 *   · sanitary_registrations    (450)
 *   · bottling_authorizations   (450 — 1 por vino)
 *
 * Depende de: WineryWinesSeeder
 */
class WineryComplianceSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    // ── Eco-certificaciones ──────────────────────────────────────────────────

    private const ECO_TYPES = ['organic', 'biodynamic', 'vegan', 'denomination', 'fairtrade', 'integrated', 'carbon_neutral'];

    private const ECO_NAMES = [
        'organic'        => ['Certificación Agricultura Ecológica — Viñas', 'Sello Ecológico Canario', 'Vino Ecológico Certificado', 'Eco Viticultura', 'Certificado Sin Fitosanitarios'],
        'biodynamic'     => ['Certificación Biodinámica — Viñedos en Ladera', 'Demeter Bodega', 'Biodinámico Canarias', 'Preparados Biodinámicos Certificados', 'Ciclos Lunares Certificados'],
        'vegan'          => ['Certificación Vegan-Friendly', 'Sello Vegano — Sin Clarificantes Animales', 'Vegan Wine Certified', 'Clarificación Mineral Certificada', 'Vegan Society Sello'],
        'denomination'   => ['Sello D.O. Gran Canaria', 'D.O. Prot. Canarias', 'Mención Calidad D.O.', 'Habilitación Operador D.O.', 'Certificado Elaborador D.O.'],
        'fairtrade'      => ['Sello Fair Trade', 'Comercio Justo Vitivinícola', 'Fair Trade Certified', 'Precio Justo Viticultor', 'Cadena Suministro Ético'],
        'integrated'     => ['Producción Integrada Canarias', 'Sello PI — Viticultura Sostenible', 'Producción Integrada Viñedo', 'Control Biológico Certificado', 'Sostenibilidad Vitícola'],
        'carbon_neutral' => ['Huella de Carbono Compensada', 'Neutro en CO₂ Certificado', 'Carbon Neutral Bodega', 'Emisiones Cero Verificadas', 'Sello Climáticamente Responsable'],
    ];

    private const ECO_BODIES = [
        'organic'        => ['CAAE', 'CCPAE', 'CRAE Canarias', 'Agrocolor'],
        'biodynamic'     => ['Demeter International', 'Biodynamic Association', 'Demeter España'],
        'vegan'          => ['The Vegan Society', 'V-Label', 'BeVeg International'],
        'denomination'   => ['Consejo Regulador D.O. Gran Canaria', 'OIVE', 'MAPA'],
        'fairtrade'      => ['Fairtrade International', 'CLAC', 'Fairtrade Ibérica'],
        'integrated'     => ['CAAE', 'Consejería de Agricultura de Canarias', 'AGROINTEGRA'],
        'carbon_neutral' => ['Bureau Veritas', 'SGS Ibérica', 'AENOR', 'Carbon Trust'],
    ];

    // Vigencia en años por tipo
    private const ECO_VALIDITY = [
        'organic' => 1, 'biodynamic' => 1, 'vegan' => 1, 'denomination' => 5,
        'fairtrade' => 1, 'integrated' => 2, 'carbon_neutral' => 1,
    ];

    // ── Registros sanitarios ─────────────────────────────────────────────────

    private const SAN_TYPES = ['elaborador', 'almacenista', 'operador', 'importador', 'exportador'];

    private const SAN_ACTIVITIES = [
        'elaborador' => 'Elaboración, crianza y embotellado de vinos tranquilos y espumosos.',
        'almacenista'=> 'Almacenamiento y comercialización de vinos a granel y embotellados.',
        'operador'   => 'Operador vitivinícola inscrito en el Registro de la Viña y el Vino — OIVE.',
        'importador' => 'Importación de vinos y mostos de terceros países.',
        'exportador' => 'Exportación de vinos y productos derivados de la uva.',
    ];

    private const SAN_AUTHORITIES = [
        'Consejería de Sanidad de Canarias',
        'AESAN — Agencia Española de Seguridad Alimentaria',
        'Agencia Canaria de la Calidad Agroalimentaria (ACCA)',
        'Fondo Español de Garantía Agraria (FEGA)',
        'Dirección General de Salud Pública Canarias',
    ];

    // ── Autorizaciones embotellado ────────────────────────────────────────────

    private const BOTTLING_CONDITIONS = [
        'Embotellado en instalaciones propias. Formatos: 375 ml, 750 ml, 1 500 ml.',
        'Embotellado en maquiladora autorizada. Formato único: 750 ml.',
        'Embotellado en bodega. Tapón de corcho natural obligatorio.',
        'Embotellado en bodega. Cápsulas de PVC prohibidas.',
        'Embotellado en instalaciones certificadas D.O. Formato: 750 ml y 500 ml.',
    ];

    public function run(): void
    {
        $this->cleanupAll();
        $now = now();

        $this->seedEcoCertifications($now);
        $this->seedSanitaryRegistrations($now);
        $this->seedBottlingAuthorizations($now);
    }

    // ── 450 eco-certificaciones ───────────────────────────────────────────────

    private function seedEcoCertifications($now): void
    {
        $rows        = [];
        $typeCounters= array_fill_keys(self::ECO_TYPES, 0);
        $yearPool    = [2020, 2021, 2022, 2022, 2023, 2023, 2024, 2024, 2025, 2025, 2026, 2026, 2026, 2026, 2026];

        for ($i = 0; $i < 450; $i++) {
            $type = self::ECO_TYPES[$i % count(self::ECO_TYPES)];
            $typeCounters[$type]++;
            $tc   = $typeCounters[$type];

            $names      = self::ECO_NAMES[$type];
            $baseName   = $names[$i % count($names)];
            $body       = self::ECO_BODIES[$type][$i % count(self::ECO_BODIES[$type])];
            $validYears = self::ECO_VALIDITY[$type];

            $issueYear  = $yearPool[$i % count($yearPool)];
            $issueMon   = str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT);
            $issueDay   = str_pad(($i % 28) + 1, 2, '0', STR_PAD_LEFT);
            $validFrom  = "$issueYear-$issueMon-$issueDay";
            $validUntil = date('Y-m-d', strtotime("+{$validYears} years", strtotime($validFrom)));
            $status     = $validUntil >= now()->toDateString() ? 'active' : 'expired';

            $certNum = strtoupper(substr($type, 0, 3)) . '-GC-' . $issueYear . '-' . str_pad($tc, 5, '0', STR_PAD_LEFT);

            $rows[] = [
                'user_id'            => self::WINERY_USER_ID,
                'name'               => "$baseName — $issueYear",
                'certification_type' => $type,
                'certifying_body'    => $body,
                'certificate_number' => $certNum,
                'valid_from'         => $validFrom,
                'valid_until'        => $validUntil,
                'status'             => $status,
                'notes'              => "Cert. $type emitida en $issueYear por $body. Registro interno Nº $tc.",
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('eco_certifications')->insert($chunk);
        }

        $activos = count(array_filter($rows, fn($r) => $r['status'] === 'active'));
        $this->command->info("✅ Ecocertificaciones: " . count($rows) . " registros ({$activos} activas)");
    }

    // ── 450 registros sanitarios ──────────────────────────────────────────────

    private function seedSanitaryRegistrations($now): void
    {
        $rows    = [];
        $tcMap   = array_fill_keys(self::SAN_TYPES, 0);
        $yearPool= [2018, 2018, 2019, 2019, 2020, 2020, 2021, 2022, 2023, 2024, 2025, 2025, 2026, 2026, 2026];

        for ($i = 0; $i < 450; $i++) {
            $type = self::SAN_TYPES[$i % count(self::SAN_TYPES)];
            $tcMap[$type]++;
            $tc   = $tcMap[$type];

            $authority   = self::SAN_AUTHORITIES[$i % count(self::SAN_AUTHORITIES)];
            $regYear     = $yearPool[$i % count($yearPool)];
            $regMon      = str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT);
            $regDay      = str_pad(($i % 28) + 1, 2, '0', STR_PAD_LEFT);
            $regDate     = "$regYear-$regMon-$regDay";

            // Renovación: 10 años para la mayoría, null para operador
            $renewalDate = $type === 'operador' ? null
                : date('Y-m-d', strtotime('+10 years', strtotime($regDate)));

            $regNum = 'REG-' . strtoupper(substr($type, 0, 3)) . '-' . $regYear . '-' . str_pad($tc, 5, '0', STR_PAD_LEFT);
            $status = ($renewalDate === null || $renewalDate >= now()->toDateString()) ? 'active' : 'expired';

            $rows[] = [
                'user_id'              => self::WINERY_USER_ID,
                'registration_number'  => $regNum,
                'registration_type'    => $type,
                'activity_description' => self::SAN_ACTIVITIES[$type],
                'registration_date'    => $regDate,
                'renewal_date'         => $renewalDate,
                'issuing_authority'    => $authority,
                'status'               => $status,
                'notes'                => "Registro sanitario de $type expedido en $regYear. Nº interno $tc.",
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('sanitary_registrations')->insert($chunk);
        }

        $activos = count(array_filter($rows, fn($r) => $r['status'] === 'active'));
        $this->command->info("✅ Registros sanitarios: " . count($rows) . " registros ({$activos} activos)");
    }

    // ── 450 autorizaciones de embotellado (1 por vino) ────────────────────────

    private function seedBottlingAuthorizations($now): void
    {
        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->orderBy('id')
            ->get(['id', 'name', 'vintage', 'volume_liters', 'status'])
            ->toArray();

        if (empty($wines)) {
            $this->command->warn('  ⚠️  Sin vinos. Saltando autorizaciones de embotellado.');
            return;
        }

        $rows = [];

        for ($i = 0; $i < 450; $i++) {
            $wine        = $wines[$i % count($wines)];
            $vintage     = $wine->vintage ?? 2024;
            $volBase     = $wine->volume_liters > 0 ? $wine->volume_liters : 5000.0;
            $authVol     = round($volBase * 1.05, 3);

            // Fechas: los de 2026 son más recientes
            $monthsBack  = 12 - ($i % 12);
            $monthsAhead = 6 + ($i % 6);
            $validFrom   = now()->subMonths($monthsBack)->format('Y-m-d');
            $validUntil  = now()->addMonths($monthsAhead)->format('Y-m-d');
            $status      = $validUntil >= now()->toDateString() ? 'active' : 'expired';

            $authNum = 'AE-GC-' . $vintage . '-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT);

            $rows[] = [
                'user_id'                  => self::WINERY_USER_ID,
                'authorization_number'     => $authNum,
                'authorization_type'       => 'embotellado',
                'wine_id'                  => $wine->id,
                'authorized_volume_liters' => $authVol,
                'valid_from'               => $validFrom,
                'valid_until'              => $validUntil,
                'issuing_authority'        => 'Consejo Regulador D.O. Gran Canaria',
                'status'                   => $status,
                'conditions'               => self::BOTTLING_CONDITIONS[$i % count(self::BOTTLING_CONDITIONS)],
                'notes'                    => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('bottling_authorizations')->insert($chunk);
        }

        $activos = count(array_filter($rows, fn($r) => $r['status'] === 'active'));
        $this->command->info("✅ Autorizaciones de embotellado: " . count($rows) . " registros ({$activos} activas)");
    }

    private function cleanupAll(): void
    {
        DB::table('eco_certifications')->where('user_id', self::WINERY_USER_ID)->delete();
        DB::table('sanitary_registrations')->where('user_id', self::WINERY_USER_ID)->delete();
        DB::table('bottling_authorizations')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
