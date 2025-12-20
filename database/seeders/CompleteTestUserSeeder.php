<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\Machinery;
use App\Models\AgriculturalActivity;
use App\Models\PlotPlanting;
use App\Models\GrapeVariety;
use App\Models\TrainingSystem;
use App\Models\SigpacUse;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
use App\Models\Fertilization;
use App\Models\Irrigation;
use App\Models\CulturalWork;
use App\Models\Observation;
use App\Models\Harvest;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\HarvestContainer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\EstimatedYield;
use App\Models\Tax;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
                    'active' => true, // Activa para Cypress
                    'description' => 'Campaña activa para tests E2E con Cypress',
                ]
            );
            
            $this->command->info("✅ Campañas creadas: 2024 (inactiva) y 2025 (activa)");
            
            // 3. Crear parcelas (20 parcelas)
            $plots = [];
            $autonomousCommunity = \App\Models\AutonomousCommunity::first();
            $province = \App\Models\Province::where('autonomous_community_id', $autonomousCommunity?->id)->first();
            $municipality = \App\Models\Municipality::where('province_id', $province?->id)->first();
            $sigpacUses = SigpacUse::whereIn('code', ['VI', 'OL', 'FR'])->get();
            
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
                        'area' => rand(1, 50) / 10, // 0.1 a 5 hectáreas
                        'active' => true,
                    ]
                );
                
                // Asignar usos SIGPAC a las parcelas
                if ($sigpacUses->isNotEmpty()) {
                    $plot->sigpacUses()->sync($sigpacUses->random(rand(1, 2))->pluck('id'));
                }
                
                $plots[] = $plot;
            }
            
            $this->command->info("✅ Parcelas creadas: " . count($plots));
            
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
                            'area_planted' => $plot->area * 0.8, // 80% del área
                            'planting_year' => 2020,
                            'planting_date' => '2020-03-15',
                            'vine_count' => rand(2000, 5000),
                            'density' => rand(3000, 4000),
                            'row_spacing' => rand(250, 300) / 100, // 2.5 a 3 metros
                            'vine_spacing' => rand(100, 150) / 100, // 1 a 1.5 metros
                            'rootstock' => '110R',
                            'training_system_id' => $trainingSystems->random()->id,
                            'irrigated' => rand(0, 1) === 1,
                            'status' => 'active',
                        ]
                    );
                }
            }
            
            $this->command->info("✅ Plantaciones creadas");
            
            // 5. Crear cuadrillas (20 cuadrillas)
            $crews = [];
            for ($i = 1; $i <= 20; $i++) {
                $crew = Crew::firstOrCreate(
                    [
                        'viticulturist_id' => $user->id,
                        'name' => "Cuadrilla Test {$i}",
                    ],
                    [
                        'description' => "Cuadrilla de prueba {$i}",
                    ]
                );
                $crews[] = $crew;
            }
            
            $this->command->info("✅ Cuadrillas creadas: " . count($crews));
            
            // 6. Crear maquinaria (20 máquinas)
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
            
            $this->command->info("✅ Maquinaria creada");
            
            // 7. Crear productos fitosanitarios PRIMERO (son globales, no tienen viticulturist_id)
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
            
            $this->command->info("✅ Productos fitosanitarios creados");
            
            // 8. Crear actividades agrícolas para campaña 2024 (tests unitarios)
            // Ahora que los productos existen, las actividades pueden tener tratamientos
            $this->createActivitiesForCampaign($user, $campaign2024, $plots, $crews, '2024');
            $this->command->info("✅ Actividades agrícolas 2024 creadas (para tests unitarios)");
            
            // 9. Crear actividades agrícolas para campaña 2025 (Cypress)
            $this->createActivitiesForCampaign($user, $campaign2025, $plots, $crews, '2025');
            $this->command->info("✅ Actividades agrícolas 2025 creadas (para Cypress)");
            
            // 10. Crear contenedores (mínimo 20)
            $this->createContainers($user);
            $this->command->info("✅ Contenedores creados");
            
            // 11. Crear clientes (mínimo 20)
            $clients = $this->createClients($user);
            $this->command->info("✅ Clientes creados: " . count($clients));
            
            // 12. Crear rendimientos estimados (mínimo 20)
            $this->createEstimatedYields($user, $campaign2024, $campaign2025);
            $this->command->info("✅ Rendimientos estimados creados");
            
            // 13. Crear facturas (mínimo 20, algunas con cosechas)
            $harvests = Harvest::whereHas('activity', function($q) use ($user) {
                $q->where('viticulturist_id', $user->id);
            })->get();
            $this->createInvoices($user, $clients, $harvests);
            $this->command->info("✅ Facturas creadas");
            
            DB::commit();
            
            $this->command->info("\n🎉 Datos poblados exitosamente!");
            if (!$userId) {
                $this->command->info("📧 Email: bernalmochonjorge@gmail.com");
                $this->command->info("🔑 Contraseña: cocoteq22");
            } else {
                $this->command->info("👤 Usuario: {$user->name} ({$user->email})");
            }
            $this->command->info("\n📊 Resumen de datos:");
            $this->command->info("   - Campañas: 2024 (inactiva) y 2025 (activa)");
            $this->command->info("   - Parcelas: " . count($plots));
            $this->command->info("   - Plantaciones: " . PlotPlanting::whereIn('plot_id', collect($plots)->pluck('id'))->count());
            $this->command->info("   - Cuadrillas: " . count($crews));
            $this->command->info("   - Maquinaria: " . Machinery::where('viticulturist_id', $user->id)->count());
            $this->command->info("   - Actividades 2024: " . AgriculturalActivity::where('campaign_id', $campaign2024->id)->count());
            $this->command->info("   - Actividades 2025: " . AgriculturalActivity::where('campaign_id', $campaign2025->id)->count());
            $this->command->info("   - Productos fitosanitarios: " . PhytosanitaryProduct::count());
            $this->command->info("   - Contenedores: " . HarvestContainer::whereDoesntHave('harvests')->count() . " disponibles");
            $this->command->info("   - Clientes: " . Client::where('user_id', $user->id)->count());
            $this->command->info("   - Facturas: " . Invoice::where('user_id', $user->id)->count());
            $this->command->info("   - Rendimientos estimados: " . EstimatedYield::whereHas('plotPlanting.plot', function($q) use ($user) {
                $q->where('viticulturist_id', $user->id);
            })->count());
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Error al crear usuario de prueba: " . $e->getMessage());
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
        $products = PhytosanitaryProduct::take(20)->get();
        
        // Crear diferentes tipos de actividades (20 de cada tipo)
        $activityTypes = [
            'phytosanitary' => 20,
            'fertilization' => 20,
            'irrigation' => 20,
            'cultural' => 20,
            'observation' => 20,
            'harvest' => 20, // Cosechas (vendimia)
        ];
        
        foreach ($activityTypes as $type => $count) {
            for ($i = 0; $i < $count; $i++) {
                $plot = $plotsCollection->random();
                $activityDate = "{$year}-" . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . "-" . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
                
                $activity = AgriculturalActivity::create([
                    'plot_id' => $plot->id,
                    'viticulturist_id' => $user->id,
                    'campaign_id' => $campaign->id,
                    'activity_type' => $type,
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
                            PhytosanitaryTreatment::firstOrCreate(
                                ['activity_id' => $activity->id],
                                [
                                    'product_id' => $products->random()->id,
                                    'dose_per_hectare' => $dosePerHectare,
                                    'total_dose' => $dosePerHectare * ($plot->area ?? 1),
                                    'area_treated' => $plot->area ?? 1,
                                    'application_method' => 'Pulverización',
                                    'target_pest' => 'Mildiu',
                                ]
                            );
                        }
                        break;
                        
                    case 'fertilization':
                        Fertilization::firstOrCreate(
                            ['activity_id' => $activity->id],
                            [
                                'fertilizer_type' => ['Orgánico', 'Mineral', 'Orgánico-Mineral'][rand(0, 2)],
                                'fertilizer_name' => 'Fertilizante Test',
                                'quantity' => rand(50, 200) / 10,
                                'npk_ratio' => '10-10-10',
                                'application_method' => 'Esparcido',
                                'area_applied' => $plot->area ?? 1,
                            ]
                        );
                        break;
                        
                    case 'irrigation':
                        Irrigation::firstOrCreate(
                            ['activity_id' => $activity->id],
                            [
                                'irrigation_method' => ['Goteo', 'Aspersión', 'Superficie'][rand(0, 2)],
                                'water_volume' => rand(1000, 5000),
                                'duration_minutes' => rand(120, 480), // 2 a 8 horas en minutos
                                'soil_moisture_before' => rand(20, 40) / 10,
                                'soil_moisture_after' => rand(50, 80) / 10,
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
                        Observation::firstOrCreate(
                            ['activity_id' => $activity->id],
                            [
                                'observation_type' => ['Fenología', 'Plagas', 'Enfermedades', 'Estado general'][rand(0, 3)],
                                'description' => "Observación de prueba en {$year}",
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
                            $totalWeight = rand(1000, 10000); // kg
                            
                            Harvest::firstOrCreate(
                                ['activity_id' => $activity->id],
                                [
                                    'plot_planting_id' => $planting->id,
                                    'harvest_start_date' => $harvestStartDate,
                                    'harvest_end_date' => $harvestEndDate,
                                    'total_weight' => $totalWeight,
                                    'yield_per_hectare' => $planting->area_planted > 0 ? round($totalWeight / $planting->area_planted, 3) : null,
                                    'baume_degree' => rand(100, 140) / 10, // 10.0 a 14.0
                                    'brix_degree' => rand(180, 250) / 10, // 18.0 a 25.0
                                    'acidity_level' => rand(30, 80) / 10, // 3.0 a 8.0
                                    'ph_level' => rand(280, 380) / 100, // 2.8 a 3.8
                                    'color_rating' => ['excelente', 'bueno', 'aceptable', 'deficiente'][rand(0, 3)],
                                    'aroma_rating' => ['excelente', 'bueno', 'aceptable', 'deficiente'][rand(0, 3)],
                                    'health_status' => ['sano', 'daño_leve', 'daño_moderado', 'daño_grave'][rand(0, 3)],
                                    'destination_type' => ['winery', 'direct_sale', 'cooperative', 'self_consumption', 'other'][rand(0, 4)],
                                    'destination' => 'Destino de prueba',
                                    'buyer_name' => rand(0, 1) === 1 ? 'Comprador Test' : null,
                                    'price_per_kg' => rand(50, 200) / 100, // 0.50 a 2.00 €/kg
                                    'total_value' => null, // Se calcula automáticamente
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
     */
    private function createContainers(User $user): void
    {
        $containerTypes = ['caja', 'pallet', 'contenedor', 'saco', 'cuba', 'other'];
        $statuses = ['empty', 'stored', 'filled', 'delivered'];
        
        for ($i = 1; $i <= 25; $i++) {
            HarvestContainer::firstOrCreate(
                [
                    'container_number' => "CONT-{$user->id}-{$i}",
                ],
                [
                    'container_type' => $containerTypes[array_rand($containerTypes)],
                    'quantity' => rand(1, 10),
                    'weight' => rand(100, 2000) / 10, // 10 a 200 kg
                    'location' => ['Almacén A', 'Almacén B', 'Campo', 'Transporte'][rand(0, 3)],
                    'status' => $statuses[array_rand($statuses)],
                    'filled_date' => rand(0, 1) === 1 ? now()->subDays(rand(1, 60))->format('Y-m-d') : null,
                    'delivery_date' => rand(0, 1) === 1 ? now()->subDays(rand(1, 30))->format('Y-m-d') : null,
                    'notes' => "Contenedor de prueba {$i}",
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
            
            // Crear 1-3 direcciones por cliente
            $addressCount = rand(1, 3);
            for ($j = 1; $j <= $addressCount; $j++) {
                $province = $provinces->random();
                $municipality = $municipalities->where('province_id', $province->id)->first() ?? $municipalities->random();
                
                ClientAddress::firstOrCreate(
                    [
                        'client_id' => $client->id,
                        'name' => ['Oficina Principal', 'Almacén', 'Sucursal', 'Casa'][$j - 1] ?? "Dirección {$j}",
                    ],
                    [
                        'first_name' => $isCompany ? null : $client->first_name,
                        'last_name' => $isCompany ? null : $client->last_name,
                        'email' => $client->email,
                        'phone' => $client->phone,
                        'address' => "Calle " . ['Mayor', 'Principal', 'Nueva', 'Vieja'][rand(0, 3)] . " " . rand(1, 100),
                        'autonomous_community_id' => $province->autonomous_community_id,
                        'province_id' => $province->id,
                        'municipality_id' => $municipality->id,
                        'postal_code' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                        'is_default' => $j === 1,
                        'is_delivery_note_address' => rand(0, 1) === 1,
                        'description' => "Dirección de prueba {$j} para cliente {$i}",
                    ]
                );
            }
        }
        
        return $clients;
    }
    
    /**
     * Crear rendimientos estimados
     */
    private function createEstimatedYields(User $user, Campaign $campaign2024, Campaign $campaign2025): void
    {
        $plantings = PlotPlanting::whereHas('plot', function($q) use ($user) {
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
     * Crear facturas con items
     */
    private function createInvoices(User $user, array $clients, $harvests): void
    {
        $taxes = Tax::where('active', true)->get();
        $defaultTax = $taxes->where('code', 'IVA')->where('rate', 21)->first() ?? $taxes->first();
        
        // Obtener algunas cosechas para facturar
        $harvestsCollection = collect($harvests);
        $harvestsToInvoice = $harvestsCollection->random(min(15, $harvestsCollection->count()));
        if (!is_array($harvestsToInvoice) && !($harvestsToInvoice instanceof \Illuminate\Support\Collection)) {
            $harvestsToInvoice = collect([$harvestsToInvoice]);
        } elseif (!($harvestsToInvoice instanceof \Illuminate\Support\Collection)) {
            $harvestsToInvoice = collect($harvestsToInvoice);
        }
        
        // Crear facturas con cosechas (10 facturas)
        $invoiceCounter = 1;
        foreach ($harvestsToInvoice->take(10) as $index => $harvest) {
            $client = collect($clients)->random();
            $clientAddress = $client->addresses->first();
            $invoiceDate = \Carbon\Carbon::now()->subDays(rand(1, 180));
            $year = $invoiceDate->year;
            
            // Generar número único de factura
            $invoiceNumber = 'FAC-' . $year . '-' . str_pad($invoiceCounter, 4, '0', STR_PAD_LEFT);
            while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
                $invoiceCounter++;
                $invoiceNumber = 'FAC-' . $year . '-' . str_pad($invoiceCounter, 4, '0', STR_PAD_LEFT);
            }
            
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'client_id' => $client->id,
                'client_address_id' => $clientAddress?->id,
                'invoice_number' => $invoiceNumber,
                'current_invoice_code' => $invoiceCounter,
                'invoice_date' => $invoiceDate->format('Y-m-d'),
                'due_date' => $invoiceDate->copy()->addDays(rand(15, 60))->format('Y-m-d'),
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_base' => 0,
                'tax_rate' => $defaultTax ? $defaultTax->rate : 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'status' => ['draft', 'sent', 'paid'][rand(0, 2)],
                'payment_status' => ['unpaid', 'partial', 'paid'][rand(0, 2)],
                'payment_type' => ['cash', 'transfer', 'check'][rand(0, 2)],
                'observations' => "Factura de prueba generada automáticamente",
            ]);
            
            // Crear item de cosecha
            $quantity = $harvest->total_weight;
            $unitPrice = $harvest->price_per_kg ?? rand(50, 200) / 100;
            $subtotal = $quantity * $unitPrice;
            $discount = $subtotal * ($client->default_discount / 100);
            $taxBase = $subtotal - $discount;
            $taxAmount = $taxBase * (($defaultTax ? $defaultTax->rate : 0) / 100);
            $total = $taxBase + $taxAmount;
            
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'harvest_id' => $harvest->id,
                'name' => ($harvest->plotPlanting->grapeVariety->name ?? 'Uva') . ' - ' . ($harvest->activity->plot->name ?? 'Parcela'),
                'description' => 'Cosecha del ' . $harvest->harvest_start_date->format('d/m/Y'),
                'sku' => 'HARV-' . $harvest->id,
                'concept_type' => 'harvest',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_percentage' => $client->default_discount,
                'discount_amount' => $discount,
                'tax_id' => $defaultTax?->id,
                'tax_name' => $defaultTax?->name,
                'tax_rate' => $defaultTax ? $defaultTax->rate : 0,
                'tax_base' => $taxBase,
                'tax_amount' => $taxAmount,
                'subtotal' => $taxBase,
                'total' => $total,
            ]);
            
            // Actualizar totales de la factura
            $invoice->update([
                'subtotal' => $taxBase,
                'discount_amount' => $discount,
                'tax_base' => $taxBase,
                'tax_amount' => $taxAmount,
                'total_amount' => $total,
            ]);
            $invoiceCounter++;
        }
        
        // Crear facturas sin cosechas (10 facturas más)
        for ($i = 0; $i < 10; $i++) {
            $client = collect($clients)->random();
            $clientAddress = $client->addresses->first();
            $invoiceDate = \Carbon\Carbon::now()->subDays(rand(1, 180));
            $year = $invoiceDate->year;
            
            // Generar número único de factura
            $invoiceNumber = 'FAC-' . $year . '-' . str_pad($invoiceCounter, 4, '0', STR_PAD_LEFT);
            while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
                $invoiceCounter++;
                $invoiceNumber = 'FAC-' . $year . '-' . str_pad($invoiceCounter, 4, '0', STR_PAD_LEFT);
            }
            
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'client_id' => $client->id,
                'client_address_id' => $clientAddress?->id,
                'invoice_number' => $invoiceNumber,
                'current_invoice_code' => $invoiceCounter,
                'invoice_date' => $invoiceDate->format('Y-m-d'),
                'due_date' => $invoiceDate->copy()->addDays(rand(15, 60))->format('Y-m-d'),
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_base' => 0,
                'tax_rate' => $defaultTax ? $defaultTax->rate : 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'status' => ['draft', 'sent', 'paid'][rand(0, 2)],
                'payment_status' => ['unpaid', 'partial', 'paid'][rand(0, 2)],
                'payment_type' => ['cash', 'transfer'][rand(0, 1)],
                'observations' => "Factura de prueba generada automáticamente",
            ]);
            
            // Crear 1-3 items por factura
            $itemCount = rand(1, 3);
            $invoiceSubtotal = 0;
            $invoiceDiscount = 0;
            $invoiceTax = 0;
            
            for ($j = 0; $j < $itemCount; $j++) {
                $itemNames = ['Servicio de Consultoría', 'Producto Vitivinícola', 'Servicio de Mantenimiento', 'Producto Agrícola'];
                $quantity = rand(1, 100) / 10;
                $unitPrice = rand(100, 1000) / 10;
                $subtotal = $quantity * $unitPrice;
                $discount = $subtotal * ($client->default_discount / 100);
                $taxBase = $subtotal - $discount;
                $taxAmount = $taxBase * (($defaultTax ? $defaultTax->rate : 0) / 100);
                $total = $taxBase + $taxAmount;
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'name' => $itemNames[array_rand($itemNames)],
                    'description' => "Item de prueba " . ($j + 1),
                    'sku' => 'ITEM-' . $invoiceCounter . '-' . ($j + 1),
                    'concept_type' => ['service', 'product', 'other'][rand(0, 2)],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_percentage' => $client->default_discount,
                    'discount_amount' => $discount,
                    'tax_id' => $defaultTax?->id,
                    'tax_name' => $defaultTax?->name,
                    'tax_rate' => $defaultTax ? $defaultTax->rate : 0,
                    'tax_base' => $taxBase,
                    'tax_amount' => $taxAmount,
                    'subtotal' => $taxBase,
                    'total' => $total,
                ]);
                
                $invoiceSubtotal += $taxBase;
                $invoiceDiscount += $discount;
                $invoiceTax += $taxAmount;
            }
            
            // Actualizar totales de la factura
            $invoice->update([
                'subtotal' => $invoiceSubtotal,
                'discount_amount' => $invoiceDiscount,
                'tax_base' => $invoiceSubtotal,
                'tax_amount' => $invoiceTax,
                'total_amount' => $invoiceSubtotal + $invoiceTax,
            ]);
        }
    }
}

