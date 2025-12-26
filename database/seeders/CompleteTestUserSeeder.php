<?php

namespace Database\Seeders;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Container;
use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\CulturalWork;
use App\Models\EstimatedYield;
use App\Models\Fertilization;
use App\Models\GrapeVariety;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicingSetting;
use App\Models\Irrigation;
use App\Models\Machinery;
use App\Models\Observation;
use App\Models\Pest;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\SigpacCode;
use App\Models\SigpacUse;
use App\Models\SupportTicket;
use App\Models\SupportTicketComment;
use App\Models\Tax;
use App\Models\TrainingSystem;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompleteTestUserSeeder extends Seeder
{
    /**
     * Crea un usuario de prueba completo con todos los datos necesarios
     * para tests unitarios (campaña 2024) y E2E con Cypress (campaña 2025)
     *
     * @param int|null $userId ID del usuario específico. Si es null, usa el usuario por defecto
     */
    public function run(int $userId = null): void
    {
        DB::beginTransaction();

        try {
            // 1. Obtener el usuario (por ID o por email por defecto)
            if ($userId) {
                $user = User::findOrFail($userId);
                $this->command->info("📌 Usando usuario existente ID: {$userId} - {$user->email}");
            } else {
                $user = User::firstOrCreate(
                    ['email' => 'bernalmochonjorge@gmail.com'],
                    [
                        'name' => 'Jorge Bernal',
                        'password' => Hash::make('cocoteq22'),
                        'role' => 'viticulturist',
                        'email_verified_at' => now(),
                        'can_login' => true,
                        'password_must_reset' => false,
                    ]
                );
            }

            $this->command->info("✅ Usuario: {$user->email}");

            // 1.1. Crear perfil de usuario
            $this->createUserProfile($user);
            $this->command->info('✅ Perfil de usuario creado');

            // 1.2. Crear configuración de facturación
            $this->createInvoicingSettings($user);
            $this->command->info('✅ Configuración de facturación creada');

            // 1.3. Asegurar que los impuestos existen
            $taxSeeder = new TaxSeeder();
            $taxSeeder->setCommand($this->command);
            $taxSeeder->run();

            // 2. Crear campañas (2024 para tests unitarios, 2025 para Cypress)
            $campaign2024 = Campaign::firstOrCreate(
                [
                    'viticulturist_id' => $user->id,
                    'year' => 2024,
                ],
                [
                    'name' => 'Campaña 2024',
                    'start_date' => '2024-01-01',
                    'end_date' => '2024-12-31',
                    'active' => false,
                    'description' => 'Campaña histórica para tests unitarios',
                ]
            );

            $campaign2025 = Campaign::firstOrCreate(
                [
                    'viticulturist_id' => $user->id,
                    'year' => 2025,
                ],
                [
                    'name' => 'Campaña 2025',
                    'start_date' => '2025-01-01',
                    'end_date' => '2025-12-31',
                    'active' => true,  // Activa para Cypress
                    'description' => 'Campaña activa para tests E2E con Cypress',
                ]
            );

            $this->command->info('✅ Campañas creadas: 2024 (inactiva) y 2025 (activa)');

            // 3. Crear parcelas (20 parcelas)
            $plots = [];
            $autonomousCommunity = \App\Models\AutonomousCommunity::first();
            $province = \App\Models\Province::where('autonomous_community_id', $autonomousCommunity?->id)->first();
            $municipality = \App\Models\Municipality::where('province_id', $province?->id)->first();

            for ($i = 1; $i <= 20; $i++) {
                $plot = Plot::firstOrCreate(
                    [
                        'viticulturist_id' => $user->id,
                        'name' => "Parcela Test {$i}",
                    ],
                    [
                        'description' => "Parcela de prueba número {$i} para tests",
                        'autonomous_community_id' => $autonomousCommunity?->id ?? 1,
                        'province_id' => $province?->id ?? 1,
                        'municipality_id' => $municipality?->id ?? 1,
                        'area' => rand(1, 50) / 10,  // 0.1 a 5 hectáreas
                        'active' => true,
                    ]
                );

                $plots[] = $plot;
            }

            $this->command->info('✅ Parcelas creadas: ' . count($plots));

            // 3.1. Crear códigos SIGPAC para las parcelas
            $this->createSigpacCodesForPlots($plots, $autonomousCommunity, $province, $municipality);
            $this->command->info('✅ Códigos SIGPAC creados para las parcelas');

            // 4. Crear plantaciones en las parcelas
            $grapeVarieties = GrapeVariety::take(3)->get();
            $trainingSystems = TrainingSystem::take(2)->get();

            foreach ($plots as $plot) {
                if ($grapeVarieties->isNotEmpty() && $trainingSystems->isNotEmpty()) {
                    PlotPlanting::firstOrCreate(
                        [
                            'plot_id' => $plot->id,
                            'grape_variety_id' => $grapeVarieties->random()->id,
                        ],
                        [
                            'area_planted' => $plot->area * 0.8,  // 80% del área
                            'planting_year' => 2020,
                            'planting_date' => '2020-03-15',
                            'vine_count' => rand(2000, 5000),
                            'density' => rand(3000, 4000),
                            'row_spacing' => rand(250, 300) / 100,  // 2.5 a 3 metros
                            'vine_spacing' => rand(100, 150) / 100,  // 1 a 1.5 metros
                            'rootstock' => '110R',
                            'training_system_id' => $trainingSystems->random()->id,
                            'irrigated' => rand(0, 1) === 1,
                            'status' => 'active',
                        ]
                    );
                }
            }

            $this->command->info('✅ Plantaciones creadas');

            // 5. Crear 20 viticultores
            $viticulturists = $this->createViticulturists($user);
            $this->command->info('✅ Viticultores creados: ' . count($viticulturists));

            // 6. Crear cuadrillas (20 equipos)
            $crews = [];
            for ($i = 1; $i <= 20; $i++) {
                $crew = Crew::firstOrCreate(
                    [
                        'viticulturist_id' => $user->id,
                        'name' => "Equipo Test {$i}",
                    ],
                    [
                        'description' => "Equipo de prueba {$i}",
                    ]
                );
                $crews[] = $crew;
            }

            $this->command->info('✅ Equipos creados: ' . count($crews));

            // 7. Crear 10 viticultores sin equipo (trabajadores individuales)
            $this->createViticulturistsWithoutCrew($user);
            $this->command->info('✅ Viticultores sin equipo creados: 10');

            // 8. Asignar trabajadores a cuadrillas
            $this->assignWorkersToCrews($user, $crews);
            $this->command->info('✅ Trabajadores asignados a equipos');

            // 9. Crear maquinaria (20 máquinas)
            $machineryTypes = \App\Models\MachineryType::take(3)->get();
            $machinery = [];
            for ($i = 1; $i <= 20; $i++) {
                $type = $machineryTypes->isNotEmpty() ? $machineryTypes->random() : null;
                $mach = Machinery::firstOrCreate(
                    [
                        'viticulturist_id' => $user->id,
                        'name' => "Maquinaria Test {$i}",
                    ],
                    [
                        'machinery_type_id' => $type?->id,
                        'type' => $type?->name,
                        'brand' => 'Test Brand',
                        'model' => "Model {$i}",
                        'year' => 2020 + $i,
                        'active' => true,
                    ]
                );
                $machinery[] = $mach;
            }

            $this->command->info('✅ Maquinaria creada');

            // 10. Crear productos fitosanitarios PRIMERO (son globales, no tienen viticulturist_id)
            // Esto es importante porque las actividades fitosanitarias los necesitan
            for ($i = 1; $i <= 20; $i++) {
                PhytosanitaryProduct::firstOrCreate(
                    [
                        'name' => "Producto Fitosanitario Test {$i}",
                    ],
                    [
                        'active_ingredient' => "Ingrediente {$i}",
                        'manufacturer' => 'Test Manufacturer',
                        'registration_number' => "REG-{$i}",
                        'withdrawal_period_days' => rand(7, 30),
                        'type' => 'Fungicida',
                        'toxicity_class' => 'III',
                    ]
                );
            }

            $this->command->info('✅ Productos fitosanitarios creados');

            // 11. Crear actividades agrícolas para campaña 2024 (tests unitarios)
            // Ahora que los productos existen, las actividades pueden tener tratamientos
            $this->createActivitiesForCampaign($user, $campaign2024, $plots, $crews, '2024');
            $this->command->info('✅ Actividades agrícolas 2024 creadas (para tests unitarios)');

            // 12. Crear actividades agrícolas para campaña 2025 (Cypress)
            $this->createActivitiesForCampaign($user, $campaign2025, $plots, $crews, '2025');
            $this->command->info('✅ Actividades agrícolas 2025 creadas (para Cypress)');

            // 13. Crear contenedores (mínimo 20)
            $this->createContainers($user);
            $this->command->info('✅ Contenedores creados');

            // 14. Crear clientes (mínimo 20)
            $clients = $this->createClients($user);
            $this->command->info('✅ Clientes creados: ' . count($clients));

            // 15. Crear rendimientos estimados (mínimo 20)
            $this->createEstimatedYields($user, $campaign2024, $campaign2025);
            $this->command->info('✅ Rendimientos estimados creados');

            // 16. Crear tickets de soporte
            $this->createSupportTickets($user);
            $this->command->info('✅ Tickets de soporte creados');

            // 17. Asociar usos SIGPAC a parcelas (20 asociaciones)
            $this->assignSigpacUsesToPlots($user, $plots);
            $this->command->info('✅ Usos SIGPAC asociados a parcelas');

            // 18. Crear facturas (20 facturas)
            $this->createInvoices($user, $clients);
            $this->command->info('✅ Facturas creadas');

            DB::commit();

            $this->command->info("\n🎉 Datos poblados exitosamente!");
            if (!$userId) {
                $this->command->info('📧 Email: bernalmochonjorge@gmail.com');
                $this->command->info('🔑 Contraseña: cocoteq22');
            } else {
                $this->command->info("👤 Usuario: {$user->name} ({$user->email})");
            }
            $this->command->info("\n📊 Resumen de datos:");
            $this->command->info('   - Campañas: 2024 (inactiva) y 2025 (activa)');
            $this->command->info('   - Parcelas: ' . count($plots));
            $this->command->info('   - Plantaciones: ' . PlotPlanting::whereIn('plot_id', collect($plots)->pluck('id'))->count());
            $this->command->info('   - Cuadrillas: ' . count($crews));
            $this->command->info('   - Maquinaria: ' . Machinery::where('viticulturist_id', $user->id)->count());
            $this->command->info('   - Actividades 2024: ' . AgriculturalActivity::where('campaign_id', $campaign2024->id)->count());
            $this->command->info('   - Actividades 2025: ' . AgriculturalActivity::where('campaign_id', $campaign2025->id)->count());
            $this->command->info('   - Productos fitosanitarios: ' . PhytosanitaryProduct::count());
            $this->command->info('   - Plagas/Enfermedades: ' . Pest::count());
            $this->command->info('   - Tratamientos fitosanitarios: ' . PhytosanitaryTreatment::whereHas('activity', function ($q) use ($user) {
                $q->where('viticulturist_id', $user->id);
            })->count());
            $this->command->info('   - Observaciones: ' . Observation::whereHas('activity', function ($q) use ($user) {
                $q->where('viticulturist_id', $user->id);
            })->count());
            $this->command->info('   - Contenedores: ' . Container::where('user_id', $user->id)->whereDoesntHave('harvests')->count() . ' disponibles');
            $this->command->info('   - Clientes: ' . Client::where('user_id', $user->id)->count());
            $this->command->info('   - Rendimientos estimados: ' . EstimatedYield::whereHas('plotPlanting.plot', function ($q) use ($user) {
                $q->where('viticulturist_id', $user->id);
            })->count());
            $this->command->info('   - Tickets de soporte: ' . SupportTicket::where('user_id', $user->id)->count());
            $this->command->info('   - Facturas: ' . Invoice::where('user_id', $user->id)->count());
            $this->command->info('   - Items de factura: ' . InvoiceItem::whereHas('invoice', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count());
            $this->command->info('   - Asociaciones SIGPAC Use: ' . DB::table('plot_sigpac_use')
                ->whereIn('plot_id', collect($plots)->pluck('id'))
                ->count());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error al crear usuario de prueba: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Crea actividades agrícolas para una campaña
     */
    private function createActivitiesForCampaign(
        User $user,
        Campaign $campaign,
        array $plots,
        array $crews,
        string $year
    ): void {
        $machinery = Machinery::where('viticulturist_id', $user->id)->get();
        $plotsCollection = collect($plots);
        $products = PhytosanitaryProduct::take(25)->get();

        // Obtener plagas para tratamientos y observaciones
        // Si no hay suficientes plagas, crear algunas adicionales
        $pests = Pest::all();
        if ($pests->count() < 15) {
            $this->createAdditionalPests();
            $pests = Pest::all();
        }

        // Estadios fenológicos
        $phenologicalStages = [
            'brotación', 'yema hinchada', 'hojas desplegadas', 'racimos visibles',
            'floración', 'cuajado', 'envero', 'maduración', 'senescencia'
        ];

        // Crear diferentes tipos de actividades
        // 20 actividades de cada tipo para el cuaderno digital
        $activityTypes = [
            'phytosanitary' => 20,  // Tratamientos fitosanitarios
            'fertilization' => 20,  // Fertilización
            'irrigation' => 20,  // Riego
            'cultural' => 20,  // Trabajos culturales
            'observation' => 20,  // Observaciones
            'harvest' => 20,  // Cosechas (vendimia)
        ];

        foreach ($activityTypes as $type => $count) {
            for ($i = 0; $i < $count; $i++) {
                $plot = $plotsCollection->random();
                $activityDate = "{$year}-" . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);

                // Obtener una plantación activa de la parcela si existe
                $planting = $plot->plantings()->where('status', 'active')->first();

                $activity = AgriculturalActivity::create([
                    'plot_id' => $plot->id,
                    'plot_planting_id' => $planting?->id,
                    'viticulturist_id' => $user->id,
                    'campaign_id' => $campaign->id,
                    'activity_type' => $type,
                    'phenological_stage' => $phenologicalStages[array_rand($phenologicalStages)],  // NUEVO
                    'activity_date' => $activityDate,
                    'crew_id' => rand(0, 1) === 1 && !empty($crews) ? collect($crews)->random()->id : null,
                    'machinery_id' => rand(0, 1) === 1 && $machinery->isNotEmpty() ? $machinery->random()->id : null,
                    'weather_conditions' => ['Soleado', 'Nublado', 'Lluvia', 'Viento'][rand(0, 3)],
                    'temperature' => rand(10, 30) + (rand(0, 99) / 100),
                    'notes' => "Nota de prueba para actividad {$type} en {$year}",
                ]);

                // Crear registros específicos según el tipo
                switch ($type) {
                    case 'phytosanitary':
                        if ($products->isNotEmpty()) {
                            $dosePerHectare = rand(100, 500) / 100;
                            $product = $products->random();
                            $pest = $pests->isNotEmpty() ? $pests->random() : null;

                            // GENERAR ERRORES PAC INTENCIONALMENTE EN ~30% DE LOS CASOS
                            $shouldHaveErrors = rand(1, 100) <= 30;  // 30% de probabilidad

                            PhytosanitaryTreatment::firstOrCreate(
                                ['activity_id' => $activity->id],
                                [
                                    'product_id' => $product->id,
                                    'pest_id' => $pest?->id,
                                    'dose_per_hectare' => $dosePerHectare,
                                    'total_dose' => $dosePerHectare * ($plot->area ?? 1),
                                    'area_treated' => $plot->area ?? 1,
                                    'application_method' => 'Pulverización',
                                    'target_pest' => $pest?->name ?? 'Mildiu',
                                    'wind_speed' => rand(50, 200) / 10,
                                    'humidity' => rand(400, 900) / 10,
                                    // CAMPOS PAC - Omitir algunos si shouldHaveErrors = true
                                    'treatment_justification' => $shouldHaveErrors ? null : 'Tratamiento preventivo contra '
                                        . ($pest ? $pest->name : 'mildiu')
                                        . ' detectado en la parcela. Condiciones favorables para desarrollo de la plaga.',
                                    'applicator_ropo_number' => $shouldHaveErrors && rand(0, 1) ? null : 'ROPO-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                                    'reentry_period_days' => $shouldHaveErrors && rand(0, 1) ? null : rand(1, 7),
                                    'spray_volume' => $shouldHaveErrors && rand(0, 1) ? null : rand(200, 1000) / 10,
                                ]
                            );
                        }
                        break;

                    case 'fertilization':
                        $fertilizerType = ['Orgánico', 'Mineral', 'Orgánico-Mineral'][rand(0, 2)];

                        // GENERAR ERRORES PAC INTENCIONALMENTE EN ~30% DE LOS CASOS
                        $shouldHaveErrors = rand(1, 100) <= 30;

                        Fertilization::firstOrCreate(
                            ['activity_id' => $activity->id],
                            [
                                'fertilizer_type' => $fertilizerType,
                                'fertilizer_name' => 'Fertilizante Test',
                                'quantity' => rand(50, 200) / 10,
                                'npk_ratio' => '10-10-10',
                                'application_method' => 'Esparcido',
                                'area_applied' => $shouldHaveErrors ? null : ($plot->area ?? 1),  // Omitir si tiene errores
                                // CAMPOS PAC (Unidades Fertilizantes) - Omitir algunos si shouldHaveErrors
                                'nitrogen_uf' => $shouldHaveErrors && rand(0, 1) ? null : rand(50, 200) / 10,
                                'phosphorus_uf' => $shouldHaveErrors && rand(0, 1) ? null : rand(30, 150) / 10,
                                'potassium_uf' => $shouldHaveErrors && rand(0, 1) ? null : rand(40, 180) / 10,
                                // Campos para fertilizantes orgánicos (si aplica)
                                'manure_type' => $fertilizerType === 'Orgánico' ? ['Estiércol bovino', 'Estiércol ovino', 'Compost'][rand(0, 2)] : null,
                                'burial_date' => $fertilizerType === 'Orgánico' && rand(0, 1) === 1 ? date('Y-m-d', strtotime($activityDate . ' +' . rand(1, 7) . ' days')) : null,
                                'emission_reduction_method' => $fertilizerType === 'Orgánico' && rand(0, 1) === 1 ? ['Enterrado inmediato', 'Cubierta vegetal'][rand(0, 1)] : null,
                            ]
                        );
                        break;

                    case 'irrigation':
                        // GENERAR ERRORES PAC INTENCIONALMENTE EN ~30% DE LOS CASOS
                        $shouldHaveErrors = rand(1, 100) <= 30;

                        Irrigation::firstOrCreate(
                            ['activity_id' => $activity->id],
                            [
                                'irrigation_method' => ['Goteo', 'Aspersión', 'Superficie'][rand(0, 2)],
                                'water_volume' => rand(1000, 5000),
                                'duration_minutes' => rand(120, 480),
                                'soil_moisture_before' => rand(20, 40) / 10,
                                'soil_moisture_after' => rand(50, 80) / 10,
                                // CAMPOS PAC OBLIGATORIOS - Omitir algunos si shouldHaveErrors
                                'water_source' => $shouldHaveErrors && rand(0, 1) ? null : ['Pozo', 'Embalse', 'Acequia', 'Río', 'Red municipal'][rand(0, 4)],
                                'water_concession' => $shouldHaveErrors && rand(0, 1) ? null : 'CON-' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                                'flow_rate' => $shouldHaveErrors && rand(0, 1) ? null : rand(500, 5000) / 10,
                            ]
                        );
                        break;

                    case 'cultural':
                        CulturalWork::firstOrCreate(
                            ['activity_id' => $activity->id],
                            [
                                'work_type' => ['Poda', 'Deshojado', 'Aclareo', 'Limpieza'][rand(0, 3)],
                                'description' => "Trabajo cultural de prueba en {$year}",
                            ]
                        );
                        break;

                    case 'observation':
                        $observationType = ['Fenología', 'Plagas', 'Enfermedades', 'Estado general'][rand(0, 3)];
                        // Asegurar que las observaciones de plagas/enfermedades siempre tengan pest_id
                        $observationPest = null;
                        if (($observationType === 'Plagas' || $observationType === 'Enfermedades') && $pests->isNotEmpty()) {
                            // Filtrar plagas por tipo si es necesario
                            if ($observationType === 'Plagas') {
                                $filteredPests = $pests->where('type', 'pest');
                                $observationPest = $filteredPests->isNotEmpty() ? $filteredPests->random() : $pests->random();
                            } elseif ($observationType === 'Enfermedades') {
                                $filteredPests = $pests->where('type', 'disease');
                                $observationPest = $filteredPests->isNotEmpty() ? $filteredPests->random() : $pests->random();
                            } else {
                                $observationPest = $pests->random();
                            }
                        }

                        Observation::firstOrCreate(
                            ['activity_id' => $activity->id],
                            [
                                'observation_type' => $observationType,
                                'pest_id' => $observationPest?->id,
                                'description' => "Observación de prueba en {$year}"
                                    . ($observationPest ? " - {$observationType} detectada: {$observationPest->name}. Síntomas: {$observationPest->symptoms}" : ''),
                                'severity' => ['Baja', 'Media', 'Alta'][rand(0, 2)],
                            ]
                        );
                        break;

                    case 'harvest':
                        // Las cosechas necesitan una plantación activa
                        $planting = $plot->plantings()->where('status', 'active')->first();
                        if ($planting) {
                            $harvestStartDate = $activityDate;
                            $harvestEndDate = date('Y-m-d', strtotime($harvestStartDate . ' +' . rand(1, 7) . ' days'));
                            $totalWeight = rand(1000, 10000);  // kg

                            Harvest::firstOrCreate(
                                ['activity_id' => $activity->id],
                                [
                                    'plot_planting_id' => $planting->id,
                                    'harvest_start_date' => $harvestStartDate,
                                    'harvest_end_date' => $harvestEndDate,
                                    'total_weight' => $totalWeight,
                                    'yield_per_hectare' => $planting->area_planted > 0 ? round($totalWeight / $planting->area_planted, 3) : null,
                                    'baume_degree' => rand(100, 140) / 10,  // 10.0 a 14.0
                                    'brix_degree' => rand(180, 250) / 10,  // 18.0 a 25.0
                                    'acidity_level' => rand(30, 80) / 10,  // 3.0 a 8.0
                                    'ph_level' => rand(280, 380) / 100,  // 2.8 a 3.8
                                    'color_rating' => ['excelente', 'bueno', 'aceptable', 'deficiente'][rand(0, 3)],
                                    'aroma_rating' => ['excelente', 'bueno', 'aceptable', 'deficiente'][rand(0, 3)],
                                    'health_status' => ['sano', 'daño_leve', 'daño_moderado', 'daño_grave'][rand(0, 3)],
                                    'destination_type' => ['winery', 'direct_sale', 'cooperative', 'self_consumption', 'other'][rand(0, 4)],
                                    'destination' => 'Destino de prueba',
                                    'buyer_name' => rand(0, 1) === 1 ? 'Comprador Test' : null,
                                    'price_per_kg' => rand(50, 200) / 100,  // 0.50 a 2.00 €/kg
                                    'total_value' => null,  // Se calcula automáticamente
                                    'status' => 'active',
                                    'notes' => "Cosecha de prueba en {$year}",
                                ]
                            );
                        }
                        break;
                }
            }
        }
    }

    /**
     * Crear contenedores de cosecha
     * ACTUALIZADO: Usa el nuevo modelo Container con capacity y used_capacity
     */
    private function createContainers(User $user): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $capacity = rand(500, 5000);  // 500 a 5000 kg de capacidad
            $usedCapacity = rand(0, 1) === 1 ? rand(0, $capacity) : 0;  // Algunos con capacidad usada

            Container::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'serial_number' => "CONT-{$user->id}-{$i}",
                ],
                [
                    'name' => "Contenedor Test {$i}",
                    'description' => "Contenedor de prueba número {$i}",
                    'capacity' => $capacity,
                    'used_capacity' => $usedCapacity,
                    'quantity' => rand(1, 10),
                    'unit_of_measurement_id' => 1,  // kg por defecto
                    'type_id' => 1,  // Tipo por defecto
                    'material_id' => 1,  // Material por defecto
                    'purchase_date' => rand(0, 1) === 1 ? now()->subDays(rand(30, 365))->format('Y-m-d') : null,
                    'next_maintenance_date' => rand(0, 1) === 1 ? now()->addDays(rand(30, 180)) : null,
                    'archived' => rand(0, 10) === 0,  // 10% archivados
                ]
            );
        }
    }

    /**
     * Crear clientes con direcciones
     */
    private function createClients(User $user): array
    {
        $clients = [];
        $provinces = \App\Models\Province::take(5)->get();
        $municipalities = \App\Models\Municipality::take(10)->get();

        // Nombres y apellidos para clientes individuales
        $firstNames = ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Carmen', 'Pedro', 'Laura', 'José', 'Isabel'];
        $lastNames = ['García', 'Rodríguez', 'González', 'Fernández', 'López', 'Martínez', 'Sánchez', 'Pérez', 'Gómez', 'Martín'];

        // Nombres de empresas
        $companyNames = [
            'Bodegas Rioja', 'Bodegas Ribera', 'Cooperativa Vitivinícola', 'Bodegas del Sur',
            'Viñedos Premium', 'Bodegas Familiares', 'Cavas del Norte', 'Bodegas Artesanales',
            'Viñedos Ecológicos', 'Bodegas Tradicionales', 'Cavas Selectas', 'Bodegas Modernas'
        ];

        for ($i = 1; $i <= 25; $i++) {
            $isCompany = rand(0, 1) === 1;

            if ($isCompany) {
                $client = Client::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'company_name' => $companyNames[array_rand($companyNames)] . " {$i}",
                    ],
                    [
                        'client_type' => 'company',
                        'company_document' => 'B' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                        'email' => "cliente{$i}@empresa.com",
                        'phone' => '6' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                        'default_discount' => rand(0, 15),
                        'payment_method' => ['cash', 'transfer', 'check'][rand(0, 2)],
                        'account_number' => 'ES' . str_pad(rand(0, 9999999999999999), 16, '0', STR_PAD_LEFT),
                        'has_cae' => rand(0, 1) === 1,
                        'cae_number' => rand(0, 1) === 1 ? 'CAE-' . rand(1000, 9999) : null,
                        'active' => true,
                        'notes' => "Cliente empresa de prueba {$i}",
                    ]
                );
            } else {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];

                $client = Client::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                    ],
                    [
                        'client_type' => 'individual',
                        'particular_document' => str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT) . ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Y', 'Z'][rand(0, 22)],
                        'email' => strtolower("{$firstName}.{$lastName}{$i}@email.com"),
                        'phone' => '6' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                        'default_discount' => rand(0, 10),
                        'payment_method' => ['cash', 'transfer'][rand(0, 1)],
                        'active' => true,
                        'notes' => "Cliente particular de prueba {$i}",
                    ]
                );
            }

            $clients[] = $client;

            // Crear 1-3 direcciones por cliente (similar a Create.php)
            $addressCount = rand(1, 3);

            for ($j = 0; $j < $addressCount; $j++) {
                $province = $provinces->random();
                $municipality = $municipalities->where('province_id', $province->id)->first() ?? $municipalities->random();

                // La primera dirección siempre es default (como en Create.php)
                $isDefault = $j === 0;

                // Crear dirección usando el método create() como en Create.php (solo campos que usa la app)
                $client->addresses()->create([
                    'address' => 'Calle ' . ['Mayor', 'Principal', 'Nueva', 'Vieja', 'Real', 'San José'][rand(0, 5)] . ' ' . rand(1, 200),
                    'postal_code' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                    'municipality_id' => $municipality->id,
                    'province_id' => $province->id,
                    'autonomous_community_id' => $province->autonomous_community_id,
                    'is_default' => $isDefault,
                    'description' => $j === 0 ? 'Dirección principal del cliente' : 'Dirección adicional ' . ($j + 1) . " para cliente {$i}",
                ]);
            }
        }

        return $clients;
    }

    /**
     * Crear rendimientos estimados
     */
    private function createEstimatedYields(User $user, Campaign $campaign2024, Campaign $campaign2025): void
    {
        $plantings = PlotPlanting::whereHas('plot', function ($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })->where('status', 'active')->get();

        foreach ([$campaign2024, $campaign2025] as $campaign) {
            foreach ($plantings->take(15) as $planting) {
                $estimatedTotalYield = rand(500, 5000);
                $estimatedPerHectare = $planting->area_planted > 0 ? round($estimatedTotalYield / $planting->area_planted, 3) : rand(3000, 12000) / 10;

                EstimatedYield::firstOrCreate(
                    [
                        'plot_planting_id' => $planting->id,
                        'campaign_id' => $campaign->id,
                    ],
                    [
                        'estimated_total_yield' => $estimatedTotalYield,
                        'estimated_yield_per_hectare' => $estimatedPerHectare,
                        'estimation_date' => $campaign->start_date,
                        'estimated_by' => $user->id,
                        'status' => 'confirmed',
                        'notes' => "Estimación de prueba para campaña {$campaign->year}",
                    ]
                );
            }
        }
    }

    /**
     * Crear perfil de usuario
     */
    private function createUserProfile(User $user): void
    {
        $provinces = \App\Models\Province::take(5)->get();
        $province = $provinces->random();

        UserProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'address' => 'Calle Principal ' . rand(1, 100),
                'city' => 'Ciudad Test',
                'postal_code' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'province_id' => $province->id,
                'country' => 'España',
                'phone' => '6' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
            ]
        );
    }

    /**
     * Crear configuración de facturación
     */
    private function createInvoicingSettings(User $user): void
    {
        InvoicingSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'invoice_prefix' => 'FAC-{YEAR}-',
                'invoice_padding' => 4,
                'invoice_counter' => 1,
                'invoice_year_reset' => true,
                'delivery_note_prefix' => 'ALB-{YEAR}-',
                'delivery_note_padding' => 4,
                'delivery_note_counter' => 1,
                'delivery_note_year_reset' => true,
                'last_reset_year' => now()->year,
            ]
        );
    }

    /**
     * Crear 20 viticultores
     */
    private function createViticulturists(User $user): array
    {
        $viticulturists = [];
        $names = ['Pedro', 'María', 'Luis', 'Ana', 'Carlos', 'Carmen', 'José', 'Laura', 'Juan', 'Isabel',
            'Miguel', 'Elena', 'Francisco', 'Marta', 'Antonio', 'Sofía', 'Manuel', 'Lucía', 'David', 'Paula'];
        $lastNames = ['García', 'Rodríguez', 'González', 'Fernández', 'López', 'Martínez', 'Sánchez', 'Pérez', 'Gómez', 'Martín'];

        for ($i = 1; $i <= 20; $i++) {
            $firstName = $names[($i - 1) % count($names)];
            $lastName = $lastNames[($i - 1) % count($lastNames)];

            $viticulturist = User::firstOrCreate(
                ['email' => "viticultor{$i}@test.com"],
                [
                    'name' => "{$firstName} {$lastName}",
                    'password' => Hash::make('password'),
                    'role' => 'viticulturist',
                    'email_verified_at' => now(),
                    'can_login' => true,
                    'password_must_reset' => false,
                ]
            );

            $viticulturists[] = $viticulturist;
        }

        return $viticulturists;
    }

    /**
     * Crear 10 viticultores sin equipo (trabajadores individuales)
     */
    private function createViticulturistsWithoutCrew(User $user): void
    {
        $names = ['Roberto', 'Patricia', 'Fernando', 'Cristina', 'Javier', 'Beatriz', 'Álvaro', 'Natalia', 'Rubén', 'Silvia'];
        $lastNames = ['Ruiz', 'Díaz', 'Moreno', 'Álvarez', 'Jiménez', 'Muñoz', 'Romero', 'Alonso', 'Navarro', 'Torres'];

        for ($i = 1; $i <= 10; $i++) {
            $firstName = $names[($i - 1) % count($names)];
            $lastName = $lastNames[($i - 1) % count($lastNames)];

            $viticulturist = User::firstOrCreate(
                ['email' => "viticultor-sin-equipo{$i}@test.com"],
                [
                    'name' => "{$firstName} {$lastName}",
                    'password' => Hash::make('password'),
                    'role' => 'viticulturist',
                    'email_verified_at' => now(),
                    'can_login' => true,
                    'password_must_reset' => false,
                ]
            );

            // Crear como trabajador individual (sin crew_id)
            CrewMember::firstOrCreate(
                [
                    'viticulturist_id' => $viticulturist->id,
                    'assigned_by' => $user->id,
                    'crew_id' => null,
                ],
                [
                    'phytosanitary_license_number' => rand(0, 1) === 1 ? 'LIC-' . rand(1000, 9999) : null,
                    'license_expiry_date' => rand(0, 1) === 1 ? now()->addMonths(rand(1, 24))->format('Y-m-d') : null,
                ]
            );
        }
    }

    /**
     * Asignar trabajadores a cuadrillas
     */
    private function assignWorkersToCrews(User $user, array $crews): void
    {
        $workers = User::where('role', 'viticulturist')
            ->where('id', '!=', $user->id)
            ->whereDoesntHave('crewMemberships')
            ->take(30)
            ->get();

        if ($workers->isEmpty()) {
            // Crear trabajadores adicionales si no hay suficientes
            for ($i = 1; $i <= 30; $i++) {
                $worker = User::firstOrCreate(
                    ['email' => "crewworker{$i}@test.com"],
                    [
                        'name' => "Trabajador Cuadrilla {$i}",
                        'password' => Hash::make('password'),
                        'role' => 'viticulturist',
                        'email_verified_at' => now(),
                        'can_login' => false,
                        'password_must_reset' => true,
                    ]
                );
                $workers->push($worker);
            }
        }

        $workersCollection = collect($workers);
        foreach ($crews as $crew) {
            // Asignar 2-5 trabajadores por cuadrilla
            $workersPerCrew = rand(2, 5);
            $crewWorkers = $workersCollection->random(min($workersPerCrew, $workersCollection->count()));

            foreach ($crewWorkers as $worker) {
                CrewMember::firstOrCreate(
                    [
                        'crew_id' => $crew->id,
                        'viticulturist_id' => $worker->id,
                        'assigned_by' => $user->id,
                    ],
                    [
                        'phytosanitary_license_number' => rand(0, 1) === 1 ? 'LIC-' . rand(1000, 9999) : null,
                        'license_expiry_date' => rand(0, 1) === 1 ? now()->addMonths(rand(1, 24))->format('Y-m-d') : null,
                    ]
                );
            }
        }
    }

    /**
     * Asociar usos SIGPAC a parcelas (20 asociaciones)
     */
    private function assignSigpacUsesToPlots(User $user, array $plots): void
    {
        // Obtener todos los usos SIGPAC disponibles
        $sigpacUses = SigpacUse::all();

        if ($sigpacUses->isEmpty()) {
            $this->command->warn('⚠️  No hay usos SIGPAC disponibles. Ejecuta el SigpacUseSeeder primero.');
            return;
        }

        $plotsCollection = collect($plots);
        $associationsCount = 0;
        $targetAssociations = 20;
        $maxAttempts = 200;  // Límite de intentos para evitar loops infinitos
        $attempts = 0;

        // Asociar usos SIGPAC a parcelas (máximo 20 asociaciones)
        while ($associationsCount < $targetAssociations && $attempts < $maxAttempts) {
            $plot = $plotsCollection->random();
            $sigpacUse = $sigpacUses->random();

            // Verificar que no exista ya esta asociación
            $exists = DB::table('plot_sigpac_use')
                ->where('plot_id', $plot->id)
                ->where('sigpac_use_id', $sigpacUse->id)
                ->exists();

            if (!$exists) {
                try {
                    $plot->sigpacUses()->attach($sigpacUse->id);
                    $associationsCount++;
                } catch (\Exception $e) {
                    // Si falla por alguna razón, continuar
                    // Puede ser que se haya creado entre la verificación y el attach
                }
            }

            $attempts++;
        }
    }

    /**
     * Crear facturas (20 facturas con items)
     */
    private function createInvoices(User $user, array $clients): void
    {
        if (empty($clients)) {
            $this->command->warn('⚠️  No hay clientes disponibles para crear facturas.');
            return;
        }

        $settings = InvoicingSetting::getOrCreateForUser($user->id);
        $harvests = Harvest::whereHas('activity', function ($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })->get();

        $taxes = Tax::all();
        $statuses = ['draft', 'sent', 'paid', 'cancelled', 'corrective'];
        $paymentStatuses = ['unpaid', 'partial', 'paid', 'overdue'];
        $paymentTypes = ['cash', 'transfer', 'check', 'other'];

        for ($i = 1; $i <= 20; $i++) {
            $client = collect($clients)->random();
            $clientAddress = $client->addresses()->where('is_default', true)->first()
                ?? $client->addresses()->first();

            $invoiceDate = now()->subDays(rand(0, 180));
            $status = $statuses[array_rand($statuses)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

            // Generar código de albarán
            $deliveryNoteCode = $settings->generateAndIncrementDeliveryNoteCode();

            // Calcular totales
            $subtotal = 0;
            $discountAmount = 0;
            $taxAmount = 0;

            // Crear 1-4 items por factura
            $itemsCount = rand(1, 4);
            $items = [];

            for ($j = 0; $j < $itemsCount; $j++) {
                $harvest = $harvests->isNotEmpty() && rand(0, 1) === 1 ? $harvests->random() : null;
                $tax = $taxes->isNotEmpty() ? $taxes->random() : null;

                $quantity = rand(10, 1000) / 10;  // 1.0 a 100.0
                $unitPrice = rand(50, 500) / 100;  // 0.50 a 5.00 €
                $discountPercentage = rand(0, 20);  // 0% a 20%

                $itemSubtotal = $quantity * $unitPrice;
                $itemDiscount = $itemSubtotal * ($discountPercentage / 100);
                $itemSubtotalAfterDiscount = $itemSubtotal - $itemDiscount;

                $taxRate = $tax ? $tax->rate : 0;
                $itemTax = $itemSubtotalAfterDiscount * ($taxRate / 100);

                $subtotal += $itemSubtotalAfterDiscount;
                $discountAmount += $itemDiscount;
                $taxAmount += $itemTax;

                $items[] = [
                    'harvest_id' => $harvest?->id,
                    'name' => $harvest ? "Uva {$harvest->activity->plot->name}" : "Producto Test {$i}-{$j}",
                    'description' => $harvest ? "Cosecha de {$harvest->activity->plot->name}" : "Descripción del producto {$i}-{$j}",
                    'sku' => 'SKU-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'concept_type' => $harvest ? 'harvest' : 'other',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_percentage' => $discountPercentage,
                    'tax_id' => $tax?->id,
                    'tax_name' => $tax?->name,
                    'tax_rate' => $taxRate,
                ];
            }

            $totalAmount = $subtotal + $taxAmount;

            // Generar número de factura solo si el status lo requiere y no existe ya
            $invoiceNumber = null;
            if (in_array($status, ['sent', 'paid'])) {
                // Intentar generar un número único
                $maxAttempts = 10;
                $attempt = 0;
                do {
                    $invoiceNumber = $settings->generateAndIncrementInvoiceCode();
                    $exists = Invoice::where('invoice_number', $invoiceNumber)->exists();
                    $attempt++;
                } while ($exists && $attempt < $maxAttempts);

                // Si después de varios intentos sigue duplicado, usar null
                if ($exists) {
                    $invoiceNumber = null;
                }
            }

            // Crear factura
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'client_id' => $client->id,
                'client_address_id' => $clientAddress?->id,
                'invoice_number' => $invoiceNumber,
                'delivery_note_code' => $deliveryNoteCode,
                'invoice_date' => $invoiceDate,
                'delivery_note_date' => $invoiceDate,
                'payment_date' => $paymentStatus === 'paid' ? $invoiceDate->copy()->addDays(rand(1, 30)) : null,
                'order_date' => $invoiceDate,
                'billing_address' => $clientAddress
                    ? "{$clientAddress->address}, {$clientAddress->postal_code} " . ($clientAddress->municipality->name ?? '')
                    : null,
                'billing_first_name' => $client->first_name,
                'billing_last_name' => $client->last_name,
                'billing_email' => $client->email,
                'billing_phone' => $client->phone,
                'billing_company_name' => $client->company_name,
                'billing_company_document' => $client->company_document ?? $client->particular_document,
                'billing_postal_code' => $clientAddress?->postal_code,
                'billing_city' => $clientAddress?->municipality?->name,
                'billing_state' => $clientAddress?->province?->name,
                'billing_country' => 'España',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_base' => $subtotal,
                'tax_rate' => $taxAmount > 0 ? ($taxAmount / $subtotal) * 100 : 0,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_type' => $paymentStatus === 'paid' ? $paymentTypes[array_rand($paymentTypes)] : null,
                'delivery_status' => ['pending', 'in_transit', 'delivered'][rand(0, 2)],
                'observations' => rand(0, 1) === 1 ? "Observaciones de prueba para factura {$i}" : null,
            ]);

            // Crear items de la factura
            foreach ($items as $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'harvest_id' => $itemData['harvest_id'],
                    'name' => $itemData['name'],
                    'description' => $itemData['description'],
                    'sku' => $itemData['sku'],
                    'concept_type' => $itemData['concept_type'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_percentage' => $itemData['discount_percentage'],
                    'tax_id' => $itemData['tax_id'],
                    'tax_name' => $itemData['tax_name'],
                    'tax_rate' => $itemData['tax_rate'],
                    'status' => 'active',
                    'payment_status' => in_array($paymentStatus, ['unpaid', 'partial', 'paid']) ? $paymentStatus : 'unpaid',
                    'delivery_status' => 'pending',
                ]);
            }
        }
    }

    /**
     * Crear plagas adicionales si no hay suficientes
     */
    private function createAdditionalPests(): void
    {
        $additionalPests = [
            [
                'type' => 'pest',
                'name' => 'Cochinilla',
                'scientific_name' => 'Planococcus ficus',
                'description' => 'Insecto que se alimenta de la savia de la vid, especialmente en brotes y racimos.',
                'symptoms' => 'Presencia de melaza, fumagina, debilitamiento de la planta.',
                'lifecycle' => 'Varias generaciones al año, especialmente en verano.',
                'risk_months' => [6, 7, 8, 9],
                'threshold' => '5% de brotes afectados',
                'prevention_methods' => 'Control biológico, poda sanitaria, tratamientos específicos.',
                'active' => true,
            ],
            [
                'type' => 'pest',
                'name' => 'Trips',
                'scientific_name' => 'Frankliniella occidentalis',
                'description' => 'Pequeño insecto que causa daños en hojas y racimos.',
                'symptoms' => 'Manchas plateadas en hojas, deformación de racimos.',
                'lifecycle' => 'Múltiples generaciones, especialmente en primavera-verano.',
                'risk_months' => [4, 5, 6, 7, 8],
                'threshold' => '10 trips/hoja o 5% de racimos afectados',
                'prevention_methods' => 'Trampas azules, control biológico, tratamientos preventivos.',
                'active' => true,
            ],
            [
                'type' => 'pest',
                'name' => 'Pulgón',
                'scientific_name' => 'Aphis fabae',
                'description' => 'Insecto chupador que se alimenta de la savia de hojas y brotes.',
                'symptoms' => 'Hojas enrolladas, presencia de melaza, fumagina.',
                'lifecycle' => 'Varias generaciones al año, especialmente en primavera.',
                'risk_months' => [4, 5, 6, 7],
                'threshold' => '10% de brotes afectados',
                'prevention_methods' => 'Control biológico con mariquitas, tratamientos específicos.',
                'active' => true,
            ],
            [
                'type' => 'disease',
                'name' => 'Eutipiosis',
                'scientific_name' => 'Eutypa lata',
                'description' => 'Enfermedad fúngica que causa muerte regresiva de brazos y tronco.',
                'symptoms' => 'Hojas pequeñas y cloróticas, muerte de brazos, cancros en tronco.',
                'lifecycle' => 'Infección por heridas de poda, desarrollo lento durante años.',
                'risk_months' => [11, 12, 1, 2, 3],
                'threshold' => 'Cualquier síntoma visible',
                'prevention_methods' => 'Poda en tiempo seco, protección de heridas, eliminación de material afectado.',
                'active' => true,
            ],
            [
                'type' => 'disease',
                'name' => 'Yesca',
                'scientific_name' => 'Phaeomoniella chlamydospora',
                'description' => 'Enfermedad fúngica que causa decaimiento y muerte de la vid.',
                'symptoms' => 'Hojas con manchas tigre, decaimiento progresivo, muerte de plantas.',
                'lifecycle' => 'Infección por heridas, desarrollo interno del hongo.',
                'risk_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                'threshold' => 'Cualquier síntoma visible',
                'prevention_methods' => 'Material de plantación sano, evitar heridas, tratamientos preventivos.',
                'active' => true,
            ],
            [
                'type' => 'disease',
                'name' => 'Excoriosis',
                'scientific_name' => 'Phomopsis viticola',
                'description' => 'Enfermedad fúngica que afecta principalmente a sarmientos y racimos.',
                'symptoms' => 'Manchas negras en sarmientos, racimos con bayas negras y momificadas.',
                'lifecycle' => 'Infección en primavera, desarrollo durante el verano.',
                'risk_months' => [4, 5, 6, 7, 8],
                'threshold' => '5% de sarmientos afectados',
                'prevention_methods' => 'Poda sanitaria, eliminación de material afectado, tratamientos preventivos.',
                'active' => true,
            ],
            [
                'type' => 'pest',
                'name' => 'Lobesia botrana (2ª generación)',
                'scientific_name' => 'Lobesia botrana',
                'description' => 'Segunda generación de la polilla del racimo, más dañina que la primera.',
                'symptoms' => 'Racimos con telarañas, bayas perforadas, presencia de larvas.',
                'lifecycle' => 'Segunda generación en junio-julio sobre racimos verdes.',
                'risk_months' => [6, 7],
                'threshold' => '3% de racimos afectados',
                'prevention_methods' => 'Tratamientos específicos, confusión sexual, trampas.',
                'active' => true,
            ],
            [
                'type' => 'disease',
                'name' => 'Podredumbre ácida',
                'scientific_name' => 'Acetobacter / Gluconobacter',
                'description' => 'Enfermedad bacteriana que causa podredumbre de racimos en maduración.',
                'symptoms' => 'Racimos con olor a vinagre, bayas blandas y acuosas.',
                'lifecycle' => 'Favorecida por daños en bayas y alta humedad.',
                'risk_months' => [8, 9, 10],
                'threshold' => 'Cualquier presencia en racimos',
                'prevention_methods' => 'Control de plagas que dañan bayas, ventilación, aclareo.',
                'active' => true,
            ],
        ];

        foreach ($additionalPests as $pestData) {
            Pest::firstOrCreate(
                ['name' => $pestData['name']],
                $pestData
            );
        }
    }

    /**
     * Crear tickets de soporte
     */
    private function createSupportTickets(User $user): void
    {
        $ticketTypes = ['bug', 'feature', 'improvement', 'question'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['open', 'in_progress', 'resolved', 'closed'];

        for ($i = 1; $i <= 10; $i++) {
            $status = $statuses[array_rand($statuses)];
            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'title' => "Ticket de soporte {$i}",
                'description' => "Descripción del ticket de soporte número {$i}. Este es un ticket de prueba para verificar el funcionamiento del sistema de soporte.",
                'type' => $ticketTypes[array_rand($ticketTypes)],
                'status' => $status,
                'priority' => $priorities[array_rand($priorities)],
                'resolved_at' => in_array($status, ['resolved', 'closed']) ? now()->subDays(rand(1, 30)) : null,
                'closed_at' => $status === 'closed' ? now()->subDays(rand(1, 15)) : null,
            ]);

            // Agregar comentarios a algunos tickets
            if (rand(0, 1) === 1) {
                SupportTicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'comment' => "Comentario de prueba para el ticket {$i}",
                ]);
            }
        }
    }

    /**
     * Crear códigos SIGPAC para las parcelas
     */
    private function createSigpacCodesForPlots(
        array $plots,
        $autonomousCommunity,
        $province,
        $municipality
    ): void {
        foreach ($plots as $index => $plot) {
            // Generar un código SIGPAC único para cada parcela
            $polygon = str_pad($index + 1, 2, '0', STR_PAD_LEFT);  // 01, 02, 03...
            $parcel = str_pad(($index * 5) + 1, 5, '0', STR_PAD_LEFT);  // 00001, 00006, 00011...
            $enclosure = '001';  // Recinto 1 por defecto

            $codeFields = [
                'code_autonomous_community' => str_pad($autonomousCommunity?->id ?? 13, 2, '0', STR_PAD_LEFT),
                'code_province' => str_pad($province?->id ?? 28, 2, '0', STR_PAD_LEFT),
                'code_municipality' => str_pad($municipality?->id ?? 79, 3, '0', STR_PAD_LEFT),
                'code_aggregate' => '0',
                'code_zone' => '0',
                'code_polygon' => $polygon,
                'code_plot' => $parcel,
                'code_enclosure' => $enclosure,
            ];

            // Construir el código completo de 19 dígitos
            $fullCode = \App\Models\SigpacCode::buildCodeFromFields($codeFields);

            $sigpacCode = \App\Models\SigpacCode::firstOrCreate(
                [
                    'code_autonomous_community' => $codeFields['code_autonomous_community'],
                    'code_province' => $codeFields['code_province'],
                    'code_municipality' => $codeFields['code_municipality'],
                    'code_polygon' => $polygon,
                    'code_plot' => $parcel,
                    'code_enclosure' => $enclosure,
                ],
                [
                    'code_aggregate' => '0',
                    'code_zone' => '0',
                    'code' => $fullCode,  // Código completo de 19 dígitos
                ]
            );

            // Asociar el código SIGPAC a la parcela usando la tabla pivot multipart_plot_sigpac
            // Verificar si ya existe la asociación
            $exists = DB::table('multipart_plot_sigpac')
                ->where('plot_id', $plot->id)
                ->where('sigpac_code_id', $sigpacCode->id)
                ->exists();

            if (!$exists) {
                DB::table('multipart_plot_sigpac')->insert([
                    'plot_id' => $plot->id,
                    'sigpac_code_id' => $sigpacCode->id,
                    'plot_geometry_id' => null,  // No tenemos geometría en el seeder
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
