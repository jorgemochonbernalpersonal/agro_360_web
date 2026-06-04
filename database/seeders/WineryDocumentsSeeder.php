<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera 450 documentos de bodega para user_id=1.
 * 8 tipos × ~56 documentos, con énfasis en años 2025–2026.
 */
class WineryDocumentsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    private const TYPES = [
        'license', 'sanitary', 'authorization', 'certification',
        'contract', 'plan', 'declaration', 'permit',
    ];

    private const TITLES = [
        'license' => [
            'Licencia de Actividad — Elaboración de Vinos',
            'Licencia Ambiental — Bodega',
            'Licencia de Apertura — Nave de Fermentación',
            'Licencia de Obras — Ampliación Depósitos',
            'Licencia de Uso — Almacén de Insumos',
            'Licencia de Actividad — Embotellado',
            'Licencia Municipal — Almacén Producto Terminado',
        ],
        'sanitary' => [
            'Registro Sanitario de Elaborador — RESA',
            'Autorización Sanitaria de Funcionamiento',
            'Informe Sanitario Favorable — Actividad Enológica',
            'Certificado de Higiene Alimentaria',
            'Registro de Establecimiento Alimentario',
            'Acta de Control Sanitario — Bodega',
            'Informe Inspección Sanitaria Anual',
        ],
        'authorization' => [
            'Autorización Operador Vitivinícola — OIVE',
            'Autorización de Embotellado',
            'Autorización de Etiquetado',
            'Autorización de Exportación a Terceros Países',
            'Autorización Uso Mención Reserva',
            'Autorización Uso Prácticas Enológicas Especiales',
            'Autorización Comercialización en D.O. Gran Canaria',
        ],
        'certification' => [
            'Certificado Agricultura Ecológica — CAAE',
            'Inscripción Denominación de Origen Gran Canaria',
            'Certificado ISO 9001 — Sistema de Calidad',
            'Certificado GlobalGAP — Buenas Prácticas Agrícolas',
            'Certificado Huella de Carbono',
            'Certificado Fair Trade',
            'Certificado Producción Integrada',
        ],
        'contract' => [
            'Contrato Seguro Responsabilidad Civil',
            'Contrato Gestión Residuos Sólidos (Orujos y Lías)',
            'Contrato Suministro Botellas y Corchos',
            'Contrato Servicio Laboratorio Analítico',
            'Contrato Transporte y Distribución',
            'Contrato Arrendamiento Parcela',
            'Contrato Mantenimiento Maquinaria Enológica',
            'Contrato Consultoría Enológica',
            'Contrato Asesoría Fiscal y Contable',
        ],
        'plan' => [
            'Plan de Autocontrol APPCC — Bodega',
            'Plan de Emergencias y Evacuación',
            'Plan de Igualdad de Género',
            'Plan de Gestión de Residuos',
            'Plan de Prevención de Riesgos Laborales',
            'Plan de Continuidad de Negocio',
            'Plan de Formación del Personal',
        ],
        'declaration' => [
            'Declaración de Cosecha',
            'Declaración Anual de Producción',
            'Declaración de Movimientos SILICIE',
            'Declaración de Existencias',
            'Declaración de Exportación',
            'Declaración de Intrastat — Operaciones Intracomunitarias',
            'Declaración de Alta en el Registro Vitivinícola',
        ],
        'permit' => [
            'Permiso Vertido Aguas Residuales',
            'Permiso Instalación Depósitos a Presión',
            'Permiso de Ruidos — Proceso Vendimia',
            'Permiso de Edificación — Ampliación Nave',
            'Permiso Uso de Agua Industrial',
            'Permiso Almacenamiento Productos Fitosanitarios',
            'Permiso Instalación Eléctrica Industrial',
        ],
    ];

    private const AUTHORITIES = [
        'license' => ['Ayuntamiento de Agaete', 'Cabildo de Gran Canaria', 'Gobierno de Canarias', 'Ayuntamiento de Las Palmas'],
        'sanitary' => ['AESAN', 'Consejería de Sanidad de Canarias', 'Ministerio de Sanidad', 'Dirección General de Salud Pública'],
        'authorization' => ['OIVE', 'Consejo Regulador D.O. Gran Canaria', 'MAPA', 'Gobierno de Canarias', 'Agencia Tributaria'],
        'certification' => ['CAAE', 'Consejo Regulador D.O. Gran Canaria', 'Bureau Veritas', 'AENOR', 'SGS Ibérica'],
        'contract' => ['Mapfre Seguros', 'Gestora Ambiental Canarias S.L.', 'Botella & Corcho Canarias', 'Laboratorio Atlántico', 'Tecnivin S.A.'],
        'plan' => ['Asesoría PRL Canarias', 'Consultora Enológica Atlántico', 'Recursos Humanos Externos S.L.', null],
        'declaration' => ['Consejería de Agricultura de Canarias', 'AEAT', 'Consejo Regulador D.O. Gran Canaria', 'Ministerio de Agricultura'],
        'permit' => ['Confederación Hidrográfica del Sur', 'Ayuntamiento de Agaete', 'Cabildo de Gran Canaria', 'Industria y Energía GC'],
    ];

    // Años de emisión con peso (más 2025 y 2026)
    private const YEAR_POOL = [2020, 2021, 2022, 2022, 2023, 2023, 2024, 2024, 2025, 2025, 2026, 2026, 2026, 2026, 2026];

    // Vigencia en años por tipo (null = indefinido)
    private const VALIDITY_YEARS = [
        'license' => 10,
        'sanitary' => null,
        'authorization' => 5,
        'certification' => 1,
        'contract' => 1,
        'plan' => 2,
        'declaration' => null,
        'permit' => 5,
    ];

    public function run(): void
    {
        $this->cleanup();

        $now = now();
        $rows = [];

        // Contadores por tipo para títulos únicos dentro de cada tipo
        $typeCounters = array_fill_keys(self::TYPES, 0);

        for ($i = 0; $i < 450; $i++) {
            $type = self::TYPES[$i % count(self::TYPES)];
            $typeCounters[$type]++;
            $tc = $typeCounters[$type];

            $titles = self::TITLES[$type];
            $baseTtitle = $titles[($i) % count($titles)];

            // Año de emisión (pool ponderado hacia 2025–2026)
            $issueYear = self::YEAR_POOL[$i % count(self::YEAR_POOL)];
            $issueDay = str_pad(($i % 28) + 1, 2, '0', STR_PAD_LEFT);
            $issueMon = str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT);
            $issueDate = "$issueYear-$issueMon-$issueDay";

            // Expiry date
            $validYears = self::VALIDITY_YEARS[$type];
            $expiryDate = $validYears !== null
                ? date('Y-m-d', strtotime("+{$validYears} years", strtotime($issueDate)))
                : null;

            // Autoridad emisora
            $authorities = self::AUTHORITIES[$type];
            $authority = $authorities[$i % count($authorities)];

            // Número de referencia único
            $refPrefix = strtoupper(substr($type, 0, 3));
            $refNumber = "{$refPrefix}-GC-{$issueYear}-".str_pad($tc, 5, '0', STR_PAD_LEFT);

            // Título con año para unicidad
            $title = $baseTtitle." — {$issueYear}";
            if ($tc > count(self::YEAR_POOL)) {
                $title .= " (Nº $tc)";
            }

            // Activo: expirado si expiry_date < hoy
            $active = $expiryDate === null || $expiryDate >= now()->toDateString();

            $rows[] = [
                'user_id' => self::WINERY_USER_ID,
                'title' => $title,
                'document_type' => $type,
                'reference_number' => $refNumber,
                'issue_date' => $issueDate,
                'expiry_date' => $expiryDate,
                'issuing_authority' => $authority,
                'notes' => "Documento de tipo {$type} emitido en {$issueYear}. Nº interno: {$tc}.",
                'active' => $active,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('winery_documents')->insert($chunk);
        }

        $activos = count(array_filter($rows, fn ($r) => $r['active']));
        $doc2026 = count(array_filter($rows, fn ($r) => str_starts_with($r['issue_date'], '2026')));
        $this->command->info('✅ Documentos de bodega: '.count($rows)." registros ({$activos} activos, {$doc2026} emitidos en 2026)");
    }

    private function cleanup(): void
    {
        DB::table('winery_documents')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
