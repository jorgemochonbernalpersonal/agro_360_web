<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Rellena todas las tablas de cumplimiento, recursos y registros hasta 450 registros.
 *
 * Compartido entre ViticulturistDemoSeeder y ProducerDemoSeeder.
 * Usa el patrón "fill-to-target": cuenta los registros existentes y solo crea los que faltan.
 *
 * Uso desde un seeder maestro:
 *   $seeder = new DemoBulkFillSeeder($userId, $wineryId);
 *   $seeder->setCommand($this->command);
 *   $seeder->run();
 */
class DemoBulkFillSeeder extends Seeder
{
    private const TARGET = 450;

    private int $uid;
    private int $wineryId;

    public function __construct(int $uid, int $wineryId = 0)
    {
        $this->uid      = $uid;
        $this->wineryId = $wineryId;
    }

    public function run(): void
    {
        $now         = now();
        $plotIds     = DB::table('plots')->where('viticulturist_id', $this->uid)->pluck('id')->toArray();
        $plantingIds = DB::table('plot_plantings')->whereIn('plot_id', array_slice($plotIds, 0, 200))->pluck('id')->toArray();
        $campaigns   = DB::table('campaigns')->where('viticulturist_id', $this->uid)->orderBy('year')->pluck('id')->toArray();
        $campaignId  = DB::table('campaigns')->where('viticulturist_id', $this->uid)->where('active', true)->value('id') ?? end($campaigns) ?: null;
        $expId       = DB::table('exploitations')->where('viticulturist_id', $this->uid)->value('id');
        $productIds  = DB::table('phytosanitary_products')->pluck('id')->toArray();

        if (empty($plotIds) || !$campaignId) {
            $this->command->info('  ⚠️  Sin parcelas o campaña — omitiendo relleno masivo');
            return;
        }

        $pc = count($plotIds);
        $plc = max(count($plantingIds), 1);

        // ── Planificación y recursos ──────────────────────────────────────────
        $this->fillTo('planned_works', 'viticulturist_id', function ($i) use ($plotIds, $pc, $campaignId, $now) {
            $cats   = ['poda','fertilizacion','fitosanitario','riego','labor_cultural','observacion','vendimia','post_vendimia'];
            $pris   = ['baja','media','alta','urgente'];
            $states = ['pendiente','en_progreso','completada','completada','completada'];
            $cat    = $cats[$i % 8];
            $sta    = $states[$i % 5];
            $m      = ($i % 10) + 1;
            $d      = ($i % 26) + 1;
            $date   = sprintf('2026-%02d-%02d', $m, $d);
            return [
                'viticulturist_id' => $this->uid,
                'campaign_id'      => $campaignId,
                'plot_id'          => $plotIds[$i % $pc],
                'category'         => $cat,
                'title'            => ucfirst($cat) . " — Parcela #{$i}",
                'description'      => "Trabajo programado campaña 2026. " . ucfirst($cat) . " parcela #{$i}.",
                'planned_date'     => $date,
                'planned_end_date' => $date,
                'priority'         => $pris[$i % 4],
                'status'           => $sta,
                'notes'            => null,
                'completed_at'     => $sta === 'completada' ? $date : null,
                'created_at'       => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('crews', 'viticulturist_id', function ($i) use ($now) {
            $types = ['Poda','Vendimia','Tratamientos','Laboreo','Mantenimiento','Riego'];
            return [
                'name'              => $types[$i % 6] . ' — Cuadrilla #' . ($i + 1),
                'description'       => 'Equipo de ' . strtolower($types[$i % 6]) . ' campaña 2026',
                'viticulturist_id'  => $this->uid,
                'winery_id'         => $this->wineryId ?: $this->uid,
                'created_at'        => $now, 'updated_at' => $now,
            ];
        });

        // ── Análisis y biodiversidad ──────────────────────────────────────────
        $this->fillTo('soil_analyses', 'viticulturist_id', function ($i) use ($plotIds, $pc, $campaigns, $now) {
            $labs     = ['SGS Servicios Analíticos','Laboratorio Agroalimentario Las Palmas','CIFA Canarias','Bureau Veritas España SA'];
            $textures = ['franco-arcilloso','franco','franco-limoso','arcilloso-limoso','franco-arenoso'];
            $cId      = $campaigns[min($i % 3, count($campaigns) - 1)] ?? end($campaigns);
            $d        = str_pad(($i % 26) + 1, 2, '0', STR_PAD_LEFT);
            $m        = str_pad(($i % 4) + 1, 2, '0', STR_PAD_LEFT);
            return [
                'viticulturist_id'        => $this->uid,
                'plot_id'                 => $plotIds[$i % $pc],
                'campaign_id'             => $cId,
                'analysis_date'           => "2026-{$m}-{$d}",
                'laboratory'              => $labs[$i % 4],
                'sample_depth_cm'         => [30, 40, 50, 60][$i % 4],
                'ph'                      => round(5.8 + ($i % 15) * 0.08, 1),
                'organic_matter'          => round(1.8 + ($i % 20) * 0.12, 2),
                'nitrogen_total'          => round(0.10 + ($i % 25) * 0.008, 3),
                'phosphorus'              => round(12 + ($i % 30) * 2.0, 1),
                'potassium'               => round(160 + ($i % 40) * 15, 1),
                'calcium'                 => round(1100 + ($i % 50) * 60, 1),
                'magnesium'               => round(75 + ($i % 30) * 6, 1),
                'texture_class'           => $textures[$i % 5],
                'electrical_conductivity' => round(0.15 + ($i % 25) * 0.015, 2),
                'created_at'              => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('biodiversity_records', 'viticulturist_id', function ($i) use ($plotIds, $pc, $campaignId, $now) {
            $types   = ['flora','fauna','cubierta_vegetal'];
            $species = [
                'Rosmarinus officinalis, Thymus vulgaris, Lavandula canariensis',
                'Falco tinnunculus, Columba livia, Sylvia melanocephala',
                'Lolium perenne, Trifolium repens, Medicago sativa',
            ];
            $descs = [
                'Presencia de plantas aromáticas nativas en lindes y terrazas.',
                'Avistamiento de rapaces y pájaros indicadores de ecosistema saludable.',
                'Cubierta vegetal espontánea con gramíneas y leguminosas. BCAM 9 PAC.',
            ];
            $ti = $i % 3;
            $d  = str_pad(($i % 26) + 1, 2, '0', STR_PAD_LEFT);
            $m  = [4, 6, 8][$ti];
            return [
                'viticulturist_id' => $this->uid,
                'plot_id'          => $plotIds[$i % $pc],
                'campaign_id'      => $campaignId,
                'record_type'      => $types[$ti],
                'description'      => $descs[$ti],
                'area_m2'          => round(80 + ($i % 50) * 12, 1),
                'species'          => $species[$ti],
                'record_date'      => "2026-0{$m}-{$d}",
                'notes'            => "Parcela #{$i}. Biodiversidad favorable.",
                'created_at'       => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('phytosanitary_alerts', 'viticulturist_id', function ($i) use ($now) {
            $types      = ['mildiu','oidio','botrytis','polilla','arana_roja','excoriosis','helada','sequia','cicadela','podredumbre_acida'];
            $severities = ['baja','media','alta','urgente'];
            $sources    = ['ESTACION_FITOPATOLOGICA','SERVICIO_SANIDAD_VEGETAL','AEMET_CANARIAS','CLAVERIE_CANARIAS','DGPIF_CANARIAS'];
            $areas      = ['Gran Canaria Norte','Valle de Agaete','Medianías GC','Zona Interior GC','Gran Canaria'];
            $type       = $types[$i % 10];
            $m          = (($i % 9) + 3);
            $d          = ($i % 26) + 1;
            return [
                'viticulturist_id' => $this->uid,
                'title'            => "Alerta {$type} #{$i} — condiciones favorables 2026",
                'source'           => $sources[$i % 5],
                'alert_type'       => $type,
                'severity'         => $severities[$i % 4],
                'affected_area'    => $areas[$i % 5],
                'description'      => "Condiciones propicias para {$type}. Campaña 2026. Monitorizar parcelas.",
                'recommendations'  => "Aplicar tratamiento preventivo. Revisar umbrales en parcelas afectadas.",
                'alert_date'       => sprintf('2026-%02d-%02d', $m, $d),
                'expiry_date'      => sprintf('2026-%02d-%02d', min($m + 1, 12), $d),
                'active'           => $i % 4 !== 0,
                'created_at'       => $now, 'updated_at' => $now,
            ];
        });

        // ── Cumplimiento: aplicadores, equipos, asesores, seguros ─────────────
        $this->fillTo('field_applicators', 'viticulturist_id', function ($i) use ($now) {
            $cats = ['basic','qualified','fumigator'];
            return [
                'viticulturist_id'  => $this->uid,
                'campaign_id'       => null,
                'name'              => "Aplicador #{$i} — " . ['Juan','Pedro','Carmen','Ana','Luis'][$i % 5] . ' ' . ['García','López','Suárez','Pérez','Ramos'][$i % 5],
                'ropo_number'       => sprintf('ROPO-GC-%06d', 1000 + $i),
                'ropo_category'     => $cats[$i % 3],
                'ropo_expiry_date'  => sprintf('2027-%02d-28', ($i % 12) + 1),
                'is_advisor'        => $i % 10 === 0,
                'advisor_license'   => $i % 10 === 0 ? sprintf('ASESOR-GC-%04d', $i) : null,
                'phone'             => sprintf('+34 629 %03d %03d', $i % 999, ($i * 7) % 999),
                'email'             => null,
                'active'            => $i % 8 !== 0,
                'created_at'        => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('field_equipment', 'viticulturist_id', function ($i) use ($now) {
            $types    = ['sprayer','irrigation','tractor','harvester','mower','pruner'];
            $names    = ['Atomizador','Sistema Riego','Tractor','Vendimiadora','Desbrozadora','Podadora'];
            $entities = ['CIATCA Gran Canaria','ITV Gran Canaria','Técnico propio'];
            $t        = $i % 6;
            return [
                'viticulturist_id'      => $this->uid,
                'name'                  => $names[$t] . ' #' . ($i + 1),
                'equipment_type'        => $types[$t],
                'registration_number'   => $t < 4 ? sprintf('GC-EQ-%04d', 2000 + $i) : null,
                'purchase_date'         => sprintf('20%02d-%02d-15', 18 + ($i % 8), ($i % 12) + 1),
                'last_inspection_date'  => '2025-' . str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT) . '-15',
                'next_inspection_date'  => '2027-' . str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT) . '-15',
                'inspection_entity'     => $entities[$i % 3],
                'active'                => $i % 10 !== 0,
                'notes'                 => null,
                'created_at'            => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('advisory_memberships', 'viticulturist_id', function ($i) use ($now) {
            $specs     = ['phytosanitary','agronomy','oenology','sustainability'];
            $companies = ['AgroTécnica Canarias SL','Estudio Agronómico Atlántico','Consultoría Verde GC','Agroconsulting Islas SL'];
            return [
                'viticulturist_id' => $this->uid,
                'campaign_id'      => null,
                'advisor_name'     => ['Dra. Isabel','Ing. Marcos','Dr. Antonio','Ing. Laura'][$i % 4] . ' — Asesor #' . ($i + 1),
                'license_number'   => sprintf('ASESOR-GC-%04d', 500 + $i),
                'specialty'        => $specs[$i % 4],
                'company_name'     => $companies[$i % 4],
                'phone'            => sprintf('+34 928 %03d %03d', 500 + ($i % 499), ($i * 3) % 999),
                'email'            => "asesor{$i}@agroconsulting.es",
                'active'           => $i % 6 !== 0,
                'created_at'       => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('agri_insurances', 'viticulturist_id', function ($i) use ($now) {
            $types     = ['comprehensive','hail','frost','drought','pest'];
            $companies = ['Agroseguro','Mapfre Agro','Helvetia Agroseguro','AXA Seguros Agrarios','Línea Directa Agro'];
            $statuses  = ['active','active','active','expired','expired'];
            $t         = $i % 5;
            $yr        = $statuses[$t] === 'active' ? 2026 : 2024 + ($i % 2);
            return [
                'viticulturist_id' => $this->uid,
                'coverage_type'    => $types[$t],
                'insurance_company'=> $companies[$t],
                'policy_number'    => sprintf('POL-%d-GC-%06d', $yr, 5000 + $i),
                'start_date'       => "{$yr}-04-01",
                'end_date'         => "{$yr}-09-30",
                'insured_amount'   => round(20000 + ($i % 50) * 1000, 2),
                'premium'          => round(400 + ($i % 30) * 50, 2),
                'subsidy_amount'   => $i % 3 === 0 ? round(200 + ($i % 20) * 25, 2) : null,
                'status'           => $statuses[$t],
                'agent_name'       => ['Luis Navarro','Sofía Martel','Roberto Afonso','Ana Pérez','Carlos Vega'][$i % 5],
                'agent_phone'      => sprintf('+34 928 60%d 00%d', ($i % 9) + 1, ($i % 9) + 1),
                'covered_plots'    => null,
                'notes'            => null,
                'created_at'       => $now, 'updated_at' => $now,
            ];
        });

        // ── Documentos y entorno ──────────────────────────────────────────────
        $this->fillTo('campaign_documents', 'viticulturist_id', function ($i) use ($campaigns, $now) {
            $types = ['invoice','certificate','lab_report','authorization','map','analysis','other'];
            $cId   = $campaigns[min($i % 3, count($campaigns) - 1)] ?? end($campaigns);
            $t     = $i % 7;
            return [
                'viticulturist_id' => $this->uid,
                'campaign_id'      => $cId,
                'name'             => ucfirst(str_replace('_', ' ', $types[$t])) . ' — Documento #' . ($i + 1),
                'document_type'    => $types[$t],
                'file_path'        => 'campaign_docs/doc_' . ($i + 1) . '.pdf',
                'original_filename'=> 'documento_' . ($i + 1) . '.pdf',
                'mime_type'        => 'application/pdf',
                'file_size_kb'     => 100 + ($i % 500),
                'notes'            => null,
                'created_at'       => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('plot_environments', 'viticulturist_id', function ($i) use ($plotIds, $pc, $plantingIds, $plc, $campaigns, $now) {
            $cId  = $campaigns[min($i % 3, count($campaigns) - 1)] ?? end($campaigns);
            $zones = [null, 'N2000', 'LIC', 'ZEPA', 'Parque Natural', 'ZEC'];
            return [
                'viticulturist_id'       => $this->uid,
                'plot_id'                => $plotIds[$i % $pc],
                'plot_planting_id'       => $plantingIds[$i % $plc] ?? null,
                'campaign_id'            => $cId,
                'water_intake_nearby'    => $i % 5 === 0,
                'water_intake_distance_m'=> $i % 5 === 0 ? round(50 + ($i % 200), 2) : null,
                'protected_zone_total'   => $i % 20 === 0,
                'protected_zone_partial' => $i % 8 === 0,
                'protection_zone_type'   => $i % 8 === 0 ? $zones[$i % 6] : null,
                'buffer_zone_m'          => $i % 8 === 0 ? round(5 + ($i % 20), 2) : null,
                'slope_pct'              => round(2 + ($i % 30) * 0.5, 2),
                'erosion_risk'           => $i % 6 === 0,
                'notes'                  => null,
                'created_at'             => $now, 'updated_at' => $now,
            ];
        });

        // ── Registros oficiales ───────────────────────────────────────────────
        $this->fillTo('residue_analyses', 'viticulturist_id', function ($i) use ($plantingIds, $plc, $campaigns, $now) {
            $labs    = ['Laboratorio Agrícola Canario SA','SGS España SA','Bureau Veritas España SA','Aqualogic Canarias SL'];
            $sTypes  = ['uva','suelo','agua','hoja'];
            $cId     = $campaigns[min($i % 3, count($campaigns) - 1)] ?? end($campaigns);
            $m       = str_pad((($i % 8) + 2), 2, '0', STR_PAD_LEFT);
            $d       = str_pad(($i % 26) + 1, 2, '0', STR_PAD_LEFT);
            return [
                'campaign_id'              => $cId,
                'plot_planting_id'         => $plantingIds[$i % $plc],
                'viticulturist_id'         => $this->uid,
                'analysis_date'            => "2026-{$m}-{$d}",
                'sample_date'              => "2026-{$m}-" . str_pad(max(1, (int)$d - 3), 2, '0', STR_PAD_LEFT),
                'laboratory_name'          => $labs[$i % 4],
                'laboratory_accreditation' => $i % 4 < 3 ? sprintf('ENAC-%04d', 1000 + $i) : null,
                'sample_type'              => $sTypes[$i % 4],
                'results'                  => null,
                'overall_compliant'        => $i % 12 !== 0,
                'certificate_file'         => null,
                'notes'                    => "Análisis #{$i}. Campaña 2026. " . ($i % 12 !== 0 ? 'Conforme.' : 'Supera LMR.'),
                'active'                   => true,
                'created_at'               => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('residue_managements', 'viticulturist_id', function ($i) use ($plotIds, $pc, $campaigns, $now) {
            $materials = ['pruning_wood','grape_marc','vine_leaves','grass','other'];
            $practices = ['incorporation','composting','removal','sale','biogas','burning','other'];
            $cId       = $campaigns[min($i % 3, count($campaigns) - 1)] ?? end($campaigns);
            $m         = str_pad((($i % 10) + 1), 2, '0', STR_PAD_LEFT);
            $d         = str_pad(($i % 26) + 1, 2, '0', STR_PAD_LEFT);
            return [
                'campaign_id'        => $cId,
                'plot_id'            => $plotIds[$i % $pc],
                'plot_planting_id'   => null,
                'viticulturist_id'   => $this->uid,
                'date'               => "2026-{$m}-{$d}",
                'practice_type'      => $practices[$i % 7],
                'material_type'      => $materials[$i % 5],
                'estimated_quantity' => round(100 + ($i % 50) * 80, 1),
                'quantity_unit'      => 'kg',
                'justification'      => null,
                'notes'              => "Gestión residuos #{$i}. 2026.",
                'active'             => true,
                'created_at'         => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('energy_usages', 'viticulturist_id', function ($i) use ($campaigns, $now) {
            $eTypes = ['diesel','electricity','diesel','electricity','diesel','electricity','diesel','diesel'];
            $units  = ['liters','kwh','liters','kwh','liters','kwh','liters','liters'];
            $descs  = ['Tractor poda + laboreo','Riego goteo + bomba','Atomizador tratamientos','Instalaciones campo','Transporte vendimia','Riego verano','Laboreo suelo','Microvendimiadora'];
            $t      = $i % 8;
            $cId    = $campaigns[min($i % 3, count($campaigns) - 1)] ?? end($campaigns);
            $qty    = $eTypes[$t] === 'diesel' ? round(40 + ($i % 60) * 1.5, 1) : round(100 + ($i % 80) * 5, 1);
            $cpu    = $eTypes[$t] === 'diesel' ? 1.42 : 0.18;
            $co2    = $eTypes[$t] === 'diesel' ? round($qty * 2.68, 3) : round($qty * 0.233, 3);
            $m      = str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT);
            return [
                'campaign_id'       => $cId,
                'viticulturist_id'  => $this->uid,
                'activity_id'       => null,
                'machinery_id'      => null,
                'date'              => "2026-{$m}-15",
                'energy_type'       => $eTypes[$t],
                'unit'              => $units[$t],
                'quantity'          => $qty,
                'cost_per_unit'     => $cpu,
                'total_cost'        => round($qty * $cpu, 2),
                'co2_kg_equivalent' => $co2,
                'usage_description' => $descs[$t],
                'notes'             => null,
                'active'            => true,
                'created_at'        => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('water_concessions', 'viticulturist_id', function ($i) use ($now) {
            $types = ['subterranea','comunidad_regantes','superficial','otro'];
            $t     = $i % 4;
            return [
                'viticulturist_id'  => $this->uid,
                'campaign_id'       => null,
                'concession_type'   => $types[$t],
                'concession_number' => $t < 3 ? sprintf('GC-%s-%04d', ['SUB','CGR','SUP'][$t], 2000 + $i) : null,
                'water_body'        => ['Pozo Barranco','Canal Noreste','Arroyo Seco','Aljibe pluvial'][$t] . " #{$i}",
                'authority'         => $t < 3 ? 'CHC — Confederación Hidrográfica de Canarias' : 'No requiere concesión',
                'concession_date'   => $t < 3 ? sprintf('20%02d-06-01', 19 + ($i % 7)) : null,
                'expiry_date'       => $t < 3 ? sprintf('20%02d-05-31', 29 + ($i % 3)) : null,
                'max_volume_m3'     => round(3000 + ($i % 50) * 300, 1),
                'used_volume_m3'    => round(1500 + ($i % 40) * 200, 1),
                'surface_ha'        => round(1.5 + ($i % 20) * 0.4, 2),
                'notes'             => null,
                'active'            => true,
                'created_at'        => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('fertilization_plans', 'viticulturist_id', function ($i) use ($campaigns, $now) {
            $statuses = ['active','active','draft','draft','active'];
            $cId      = $campaigns[min($i % 3, count($campaigns) - 1)] ?? end($campaigns);
            return [
                'viticulturist_id'  => $this->uid,
                'campaign_id'       => $cId,
                'plan_year'         => 2026,
                'nitrate_zone'      => $i % 5 === 0,
                'prepared_by'       => ['Dr. Antonio Suárez','Ing. Laura Vega','Dr. Isabel Cabrera','Ing. Marcos Hernández'][$i % 4],
                'approval_date'     => $statuses[$i % 5] === 'active' ? '2026-01-' . str_pad(($i % 28) + 1, 2, '0', STR_PAD_LEFT) : null,
                'total_surface_ha'  => round(2 + ($i % 30) * 0.3, 2),
                'total_n_kg_ha'     => round(50 + ($i % 40) * 1.5, 1),
                'total_p_kg_ha'     => round(15 + ($i % 20) * 0.8, 1),
                'total_k_kg_ha'     => round(40 + ($i % 30) * 1.2, 1),
                'plan_lines'        => json_encode([['crop' => 'viña', 'n_kg_ha' => round(50 + $i % 30, 1)]]),
                'status'            => $statuses[$i % 5],
                'notes'             => null,
                'active'            => true,
                'created_at'        => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('phytosanitary_container_returns', 'viticulturist_id', function ($i) use ($productIds, $campaigns, $now) {
            $cTypes  = ['plastic','glass','cardboard','flexible'];
            $systems = ['sigfito','sigfito','field','other'];
            $cId     = $campaigns[min($i % 3, count($campaigns) - 1)] ?? end($campaigns);
            $pId     = !empty($productIds) ? $productIds[$i % count($productIds)] : null;
            $m       = str_pad((($i % 10) + 2), 2, '0', STR_PAD_LEFT);
            $d       = str_pad(($i % 26) + 1, 2, '0', STR_PAD_LEFT);
            return [
                'viticulturist_id'         => $this->uid,
                'campaign_id'              => $cId,
                'phytosanitary_product_id' => $pId,
                'date'                     => "2026-{$m}-{$d}",
                'product_name'             => 'Producto Fito #' . ($i + 1),
                'registration_number'      => sprintf('ES-%05d-%02d', 200 + $i, $i % 10),
                'container_type'           => $cTypes[$i % 4],
                'container_size_liters'    => [0.25, 0.5, 1.0, 5.0][$i % 4],
                'containers_quantity'      => 2 + ($i % 20),
                'total_weight_kg'          => round(1 + ($i % 15) * 0.8, 1),
                'collection_system'        => $systems[$i % 4],
                'collection_point'         => 'Punto SIGFITO — ' . ['Agaete','Teror','Gáldar','Arucas','Guía'][$i % 5],
                'transport_document'       => null,
                'notes'                    => null,
                'active'                   => true,
                'created_at'               => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('harvest_declarations', 'viticulturist_id', function ($i) use ($campaigns, $now) {
            $statuses = ['submitted','accepted','accepted','draft'];
            $cId      = $campaigns[min($i % 3, count($campaigns) - 1)] ?? end($campaigns);
            $yr       = 2026;
            return [
                'viticulturist_id'  => $this->uid,
                'campaign_id'       => $cId,
                'declaration_year'  => $yr,
                'declaration_date'  => "2026-10-" . str_pad(($i % 28) + 1, 2, '0', STR_PAD_LEFT),
                'submission_date'   => "2026-10-" . str_pad(($i % 28) + 1 + 3, 2, '0', STR_PAD_LEFT),
                'authority'         => 'Consejo Regulador DOP Gran Canaria',
                'reference_number'  => sprintf('DV-GC-2026-%04d', 300 + $i),
                'total_surface_ha'  => round(2 + ($i % 30) * 0.3, 2),
                'total_kg'          => round(5000 + ($i % 100) * 400, 0),
                'declaration_lines' => json_encode([['variedad' => 'Listán Negro', 'kg' => 3000 + $i * 10]]),
                'status'            => $statuses[$i % 4],
                'rejection_reason'  => null,
                'notes'             => null,
                'active'            => true,
                'created_at'        => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('harvest_byproducts', 'viticulturist_id', function ($i) use ($campaigns, $now) {
            $types = ['pomace','stem','lees','other'];
            $dests = ['distillery','composting','cooperative','authorized_landfill'];
            $cId   = $campaigns[min($i % 3, count($campaigns) - 1)] ?? end($campaigns);
            $m     = str_pad((($i % 4) + 9), 2, '0', STR_PAD_LEFT);
            $d     = str_pad(($i % 26) + 1, 2, '0', STR_PAD_LEFT);
            return [
                'viticulturist_id'   => $this->uid,
                'campaign_id'        => $cId,
                'date'               => "2026-{$m}-{$d}",
                'byproduct_type'     => $types[$i % 4],
                'quantity_kg'        => round(100 + ($i % 60) * 80, 1),
                'destination_type'   => $dests[$i % 6],
                'destination_name'   => ['Destilados GC SL','Compostería Agaete','Coop. Vitivinícola','Vertedero Municipal'][$i % 4],
                'document_reference' => $i % 3 === 0 ? sprintf('DES-2026-%04d', $i) : null,
                'notes'              => null,
                'active'             => true,
                'created_at'         => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('certifications', 'viticulturist_id', function ($i) use ($now) {
            $types  = ['ecologico','produccion_integrada','denominacion_origen','globalgap','vegan','biodynamic'];
            $bodies = ['CAAE — Canarias','Gobierno de Canarias','Consejo Regulador DOP GC','Bureau Veritas España','V-Label','Demeter'];
            $t      = $i % 6;
            return [
                'viticulturist_id'   => $this->uid,
                'certification_type' => $types[$t],
                'certifying_body'    => $bodies[$t],
                'certificate_number' => sprintf('%s-GC-2026-%04d', strtoupper(substr($types[$t], 0, 3)), $i),
                'issue_date'         => '2026-' . str_pad(($i % 6) + 1, 2, '0', STR_PAD_LEFT) . '-01',
                'expiry_date'        => '2028-' . str_pad(($i % 6) + 1, 2, '0', STR_PAD_LEFT) . '-01',
                'scope'              => 'Certificación ' . str_replace('_', ' ', $types[$t]) . " — parcelas viñedo #{$i}",
                'audit_date'         => null,
                'notes'              => null,
                'status'             => ['active','active','active','expired','pending','suspended'][$i % 6],
                'created_at'         => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('commercial_authorizations', 'viticulturist_id', function ($i) use ($expId, $now) {
            $types = ['do_registration','organic_certification','integrated_production','planting_right','replanting_right','export_authorization'];
            $t     = $i % 6;
            return [
                'viticulturist_id'     => $this->uid,
                'exploitation_id'      => $expId,
                'authorization_type'   => $types[$t],
                'authorization_code'   => sprintf('%s-GC-2026-%04d', strtoupper(substr($types[$t], 0, 3)), 100 + $i),
                'description'          => ucfirst(str_replace('_', ' ', $types[$t])) . " — autorización #{$i}",
                'issuing_body'         => ['Consejo Regulador DOP GC','CAAE Canarias','Gobierno de Canarias','FEGA','SGRA','Aduanas'][$t],
                'issue_date'           => '2026-' . str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT) . '-15',
                'expiry_date'          => $i % 3 === 0 ? null : '2028-12-31',
                'document_file'        => null,
                'notes'                => null,
                'active'               => true,
                'created_at'           => $now, 'updated_at' => $now,
            ];
        });

        $this->fillTo('cue_exports', 'viticulturist_id', function ($i) use ($expId, $campaigns, $now) {
            $periods  = ['quarterly','quarterly','quarterly','quarterly','annual'];
            $statuses = ['accepted','accepted','sent','draft','accepted'];
            $p        = $i % 5;
            $q        = ($i % 4) + 1;
            $fromM    = ($q - 1) * 3 + 1;
            $toM      = $q * 3;
            return [
                'exploitation_id'  => $expId,
                'viticulturist_id' => $this->uid,
                'campaign_year'    => 2026,
                'period_type'      => $periods[$p],
                'from_date'        => sprintf('2026-%02d-01', $fromM),
                'to_date'          => sprintf('2026-%02d-%02d', $toM, $toM === 6 || $toM === 9 ? 30 : ($toM === 12 ? 31 : 31)),
                'status'           => $statuses[$p],
                'payload_json'     => null,
                'response_json'    => null,
                'generated_at'     => $statuses[$p] !== 'draft' ? "2026-" . str_pad($toM + 1, 2, '0', STR_PAD_LEFT) . "-05 09:00:00" : null,
                'sent_at'          => $statuses[$p] !== 'draft' ? "2026-" . str_pad($toM + 1, 2, '0', STR_PAD_LEFT) . "-06 09:00:00" : null,
                'accepted_at'      => $statuses[$p] === 'accepted' ? "2026-" . str_pad($toM + 1, 2, '0', STR_PAD_LEFT) . "-08 11:00:00" : null,
                'error_message'    => null,
                'file_path'        => null,
                'created_at'       => $now, 'updated_at' => $now,
            ];
        });

        // marketed_harvests requiere harvest_id (FK) — solo rellenar si hay harvests
        $harvestIds = DB::table('harvests')
            ->whereIn('activity_id', DB::table('agricultural_activities')->where('viticulturist_id', $this->uid)->pluck('id'))
            ->pluck('id')->toArray();
        if (!empty($harvestIds)) {
            $hc = count($harvestIds);
            $this->fillTo('marketed_harvests', 'viticulturist_id', function ($i) use ($harvestIds, $hc, $campaignId, $now) {
                $dests  = ['own_winery','cooperative','third_party','other'];
                $buyers = ['Bodega Agaete','Coop. Vitivinícola GC','Bodegas Bentayga','Bodega Los Berrazales'];
                $qty    = round(200 + ($i % 80) * 50, 2);
                $price  = round(1.2 + ($i % 15) * 0.1, 4);
                return [
                    'viticulturist_id'   => $this->uid,
                    'harvest_id'         => $harvestIds[$i % $hc],
                    'campaign_id'        => $campaignId,
                    'delivery_date'      => '2026-09-' . str_pad(($i % 28) + 1, 2, '0', STR_PAD_LEFT),
                    'quantity_kg'        => $qty,
                    'destination_type'   => $dests[$i % 4],
                    'buyer_name'         => $buyers[$i % 4],
                    'buyer_rega_code'    => $i % 3 === 0 ? sprintf('REGA-GC-%04d', $i) : null,
                    'transport_document' => $i % 4 === 0 ? sprintf('ALB-2026-%04d', $i) : null,
                    'vehicle_plate'      => $i % 5 === 0 ? sprintf('GC-%04d-XX', 1000 + $i) : null,
                    'price_per_kg'       => $price,
                    'total_value'        => round($qty * $price, 2),
                    'notes'              => null,
                    'active'             => true,
                    'created_at'         => $now, 'updated_at' => $now,
                ];
            });
        }
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function fillTo(string $table, string $fkCol, callable $rowGenerator): void
    {
        $current = DB::table($table)->where($fkCol, $this->uid)->count();
        $needed  = self::TARGET - $current;

        if ($needed <= 0) {
            $this->command->info("    ✅ {$table}: {$current} (≥450)");
            return;
        }

        $rows = [];
        for ($i = 0; $i < $needed; $i++) {
            $rows[] = $rowGenerator($i + $current);
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table($table)->insert($chunk);
        }

        $this->command->info("    ✅ {$table}: +{$needed} → " . ($current + $needed));
    }
}
