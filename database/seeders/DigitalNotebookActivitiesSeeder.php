<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\AgriculturalActivity;
use App\Models\PhytosanitaryTreatment;
use App\Models\PhytosanitaryProduct;
use App\Models\Fertilization;
use App\Models\Irrigation;
use App\Models\CulturalWork;
use App\Models\Observation;
use App\Models\Harvest;
use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\Machinery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DigitalNotebookActivitiesSeeder extends Seeder
{
    /**
     * Crea actividades de todos los tipos del cuaderno digital para testear la generación de informes
     */
    public function run(): void
    {
        $this->command->info('🌱 Creando actividades del cuaderno digital...');

        // Obtener o crear usuario viticultor de prueba
        $viticulturist = User::where('email', 'viticulturist@test.com')
            ->orWhere('role', 'viticulturist')
            ->first();

        if (!$viticulturist) {
            $this->command->warn('⚠️ No se encontró usuario viticultor. Creando uno...');
            $viticulturist = User::create([
                'name' => 'Viticultor Test',
                'email' => 'viticulturist@test.com',
                'password' => bcrypt('password'),
                'role' => 'viticulturist',
                'email_verified_at' => now(),
                'can_login' => true,
            ]);
            
            if (method_exists($viticulturist, 'grantBetaAccess')) {
                $viticulturist->grantBetaAccess();
            }
        }

        // Obtener o crear campaña activa
        $campaign = Campaign::getOrCreateActiveForYear($viticulturist->id, now()->year);
        
        if (!$campaign) {
            $campaign = Campaign::create([
                'name' => 'Campaña ' . now()->year,
                'year' => now()->year,
                'viticulturist_id' => $viticulturist->id,
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
                'active' => true,
            ]);
        }

        // Obtener parcelas del usuario
        $plots = Plot::forUser($viticulturist)
            ->where('active', true)
            ->get();

        if ($plots->isEmpty()) {
            $this->command->warn('⚠️ No hay parcelas disponibles. Creando una parcela de prueba...');
            $plot = Plot::create([
                'name' => 'Parcela Test Cuaderno Digital',
                'viticulturist_id' => $viticulturist->id,
                'active' => true,
                'autonomous_community_id' => 1,
                'province_id' => 1,
                'municipality_id' => 1,
            ]);
            $plots = collect([$plot]);
        }

        $plot = $plots->first();

        // Obtener o crear plantación
        $planting = PlotPlanting::where('plot_id', $plot->id)->first();
        
        if (!$planting) {
            $this->command->warn('⚠️ No hay plantaciones. Creando una plantación de prueba...');
            $planting = PlotPlanting::create([
                'plot_id' => $plot->id,
                'grape_variety_id' => 1, // Ajustar según tus datos
                'training_system_id' => 1, // Ajustar según tus datos
                'planting_year' => now()->year - 2,
                'area_planted' => 5.0,
            ]);
        }

        // Obtener productos fitosanitarios
        $products = PhytosanitaryProduct::all();
        if ($products->isEmpty()) {
            $this->command->warn('⚠️ No hay productos fitosanitarios. Creando algunos...');
            $products = collect([
                PhytosanitaryProduct::create([
                    'name' => 'Producto Test 1',
                    'active_ingredient' => 'Ingrediente Activo Test',
                    'withdrawal_period_days' => 30,
                    'active' => true,
                ]),
                PhytosanitaryProduct::create([
                    'name' => 'Producto Test 2',
                    'active_ingredient' => 'Ingrediente Activo Test 2',
                    'withdrawal_period_days' => 21,
                    'active' => true,
                ]),
            ]);
        }

        // Obtener maquinaria
        $machinery = Machinery::forViticulturist($viticulturist->id)->active()->first();
        
        // Obtener o crear equipo
        $crew = Crew::where('viticulturist_id', $viticulturist->id)->first();
        
        if (!$crew) {
            $crew = Crew::create([
                'name' => 'Equipo Test',
                'viticulturist_id' => $viticulturist->id,
                'description' => 'Equipo de prueba para tests',
            ]);
        }

        // Crear CrewMember individual para el viticultor
        $crewMember = CrewMember::firstOrCreate(
            [
                'viticulturist_id' => $viticulturist->id,
                'assigned_by' => $viticulturist->id,
            ],
            [
                'crew_id' => null, // Individual
            ]
        );

        $this->command->info("✅ Usuario: {$viticulturist->name}");
        $this->command->info("✅ Campaña: {$campaign->name}");
        $this->command->info("✅ Parcela: {$plot->name}");

        // Fechas para distribuir las actividades a lo largo del año
        $startDate = now()->startOfYear();
        $currentDate = $startDate->copy();

        // 1. TRATAMIENTOS FITOSANITARIOS (phytosanitary)
        $this->command->info('🦠 Creando tratamientos fitosanitarios...');
        for ($i = 0; $i < 10; $i++) {
            $activityDate = $currentDate->copy()->addDays($i * 15);
            
            $activity = AgriculturalActivity::create([
                'plot_id' => $plot->id,
                'plot_planting_id' => $planting->id,
                'viticulturist_id' => $viticulturist->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'phytosanitary',
                'activity_date' => $activityDate,
                'crew_id' => $i % 2 === 0 ? $crew->id : null,
                'crew_member_id' => $i % 2 === 1 ? $crewMember->id : null,
                'machinery_id' => $machinery ? $machinery->id : null,
                'weather_conditions' => ['soleado', 'nublado', 'lluvia ligera'][$i % 3],
                'temperature' => 15 + ($i * 2),
                'notes' => "Tratamiento fitosanitario de prueba #{$i}",
            ]);

            PhytosanitaryTreatment::create([
                'activity_id' => $activity->id,
                'product_id' => $products->random()->id,
                'dose_per_hectare' => 1.5 + ($i * 0.1),
                'total_dose' => 7.5 + ($i * 0.5),
                'area_treated' => 5.0,
                'application_method' => ['pulverización', 'aplicación foliar', 'aplicación al suelo'][$i % 3],
                'target_pest' => ['oidio', 'mildiu', 'botritis', 'araña roja'][$i % 4],
                'wind_speed' => 5 + ($i * 0.5),
                'humidity' => 60 + ($i * 2),
            ]);
        }
        $this->command->info('✅ 10 tratamientos fitosanitarios creados');

        // 2. FERTILIZACIONES (fertilization)
        $this->command->info('🌿 Creando fertilizaciones...');
        for ($i = 0; $i < 8; $i++) {
            $activityDate = $currentDate->copy()->addDays($i * 20);
            
            $activity = AgriculturalActivity::create([
                'plot_id' => $plot->id,
                'plot_planting_id' => $planting->id,
                'viticulturist_id' => $viticulturist->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'fertilization',
                'activity_date' => $activityDate,
                'crew_id' => $i % 2 === 0 ? $crew->id : null,
                'crew_member_id' => $i % 2 === 1 ? $crewMember->id : null,
                'machinery_id' => $machinery ? $machinery->id : null,
                'weather_conditions' => ['soleado', 'nublado'][$i % 2],
                'temperature' => 18 + ($i * 1.5),
                'notes' => "Fertilización de prueba #{$i}",
            ]);

            Fertilization::create([
                'activity_id' => $activity->id,
                'fertilizer_type' => ['orgánico', 'mineral', 'compost'][$i % 3],
                'fertilizer_name' => "Fertilizante Test {$i}",
                'quantity' => 100 + ($i * 10),
                'npk_ratio' => ['10-20-10', '15-15-15', '20-10-10'][$i % 3],
                'application_method' => ['al suelo', 'foliar', 'fertirrigación'][$i % 3],
                'area_applied' => 5.0,
            ]);
        }
        $this->command->info('✅ 8 fertilizaciones creadas');

        // 3. RIEGOS (irrigation)
        $this->command->info('💧 Creando riegos...');
        for ($i = 0; $i < 12; $i++) {
            $activityDate = $currentDate->copy()->addDays($i * 10);
            
            $activity = AgriculturalActivity::create([
                'plot_id' => $plot->id,
                'plot_planting_id' => $planting->id,
                'viticulturist_id' => $viticulturist->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'irrigation',
                'activity_date' => $activityDate,
                'crew_id' => null,
                'crew_member_id' => $crewMember->id,
                'machinery_id' => null,
                'weather_conditions' => 'soleado',
                'temperature' => 20 + ($i * 1),
                'notes' => "Riego de prueba #{$i}",
            ]);

            Irrigation::create([
                'activity_id' => $activity->id,
                'water_volume' => 5000 + ($i * 500),
                'irrigation_method' => ['goteo', 'aspersión', 'inundación'][$i % 3],
                'duration_minutes' => 120 + ($i * 10),
                'soil_moisture_before' => 30 + ($i * 2),
                'soil_moisture_after' => 60 + ($i * 2),
            ]);
        }
        $this->command->info('✅ 12 riegos creados');

        // 4. LABORES CULTURALES (cultural)
        $this->command->info('🔧 Creando labores culturales...');
        $workTypes = ['poda', 'deshojado', 'acolchado', 'vendimia', 'despuntado', 'atado'];
        for ($i = 0; $i < 10; $i++) {
            $activityDate = $currentDate->copy()->addDays($i * 18);
            
            $activity = AgriculturalActivity::create([
                'plot_id' => $plot->id,
                'plot_planting_id' => $planting->id,
                'viticulturist_id' => $viticulturist->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'cultural',
                'activity_date' => $activityDate,
                'crew_id' => $i % 2 === 0 ? $crew->id : null,
                'crew_member_id' => $i % 2 === 1 ? $crewMember->id : null,
                'machinery_id' => $i % 3 === 0 && $machinery ? $machinery->id : null,
                'weather_conditions' => ['soleado', 'nublado', 'lluvia ligera'][$i % 3],
                'temperature' => 12 + ($i * 2),
                'notes' => "Labor cultural de prueba #{$i}",
            ]);

            CulturalWork::create([
                'activity_id' => $activity->id,
                'work_type' => $workTypes[$i % count($workTypes)],
                'hours_worked' => 4 + ($i * 0.5),
                'workers_count' => 2 + ($i % 3),
                'description' => "Descripción de la labor {$workTypes[$i % count($workTypes)]} #{$i}",
            ]);
        }
        $this->command->info('✅ 10 labores culturales creadas');

        // 5. OBSERVACIONES (observation)
        $this->command->info('👁️ Creando observaciones...');
        $observationTypes = ['plaga', 'enfermedad', 'fenología', 'general'];
        $severities = ['leve', 'moderada', 'grave'];
        for ($i = 0; $i < 15; $i++) {
            $activityDate = $currentDate->copy()->addDays($i * 8);
            
            $activity = AgriculturalActivity::create([
                'plot_id' => $plot->id,
                'plot_planting_id' => $planting->id,
                'viticulturist_id' => $viticulturist->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'observation',
                'activity_date' => $activityDate,
                'crew_id' => null,
                'crew_member_id' => $crewMember->id,
                'machinery_id' => null,
                'weather_conditions' => ['soleado', 'nublado', 'lluvia'][$i % 3],
                'temperature' => 16 + ($i * 1.2),
                'notes' => "Observación de prueba #{$i}",
            ]);

            Observation::create([
                'activity_id' => $activity->id,
                'observation_type' => $observationTypes[$i % count($observationTypes)],
                'description' => "Descripción detallada de la observación #{$i}. Se observó un comportamiento específico en la parcela.",
                'photos' => null,
                'severity' => $severities[$i % count($severities)],
                'action_taken' => $i % 2 === 0 ? "Acción tomada para la observación #{$i}" : null,
            ]);
        }
        $this->command->info('✅ 15 observaciones creadas');

        // 6. COSECHAS (harvest)
        $this->command->info('🍇 Creando cosechas...');
        for ($i = 0; $i < 5; $i++) {
            $harvestStartDate = $currentDate->copy()->addMonths(8)->addDays($i * 3);
            $harvestEndDate = $harvestStartDate->copy()->addDays(2);
            
            $activity = AgriculturalActivity::create([
                'plot_id' => $plot->id,
                'plot_planting_id' => $planting->id,
                'viticulturist_id' => $viticulturist->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'harvest',
                'activity_date' => $harvestStartDate,
                'crew_id' => $crew->id,
                'crew_member_id' => null,
                'machinery_id' => null,
                'weather_conditions' => 'soleado',
                'temperature' => 22 + ($i * 1),
                'notes' => "Cosecha de prueba #{$i}",
            ]);

            $totalWeight = 1000 + ($i * 200);
            $pricePerKg = 0.5 + ($i * 0.1);

            Harvest::create([
                'activity_id' => $activity->id,
                'plot_planting_id' => $planting->id,
                'harvest_start_date' => $harvestStartDate,
                'harvest_end_date' => $harvestEndDate,
                'total_weight' => $totalWeight,
                'yield_per_hectare' => $totalWeight / $planting->area_planted,
                'baume_degree' => 12 + ($i * 0.5),
                'brix_degree' => 22 + ($i * 0.5),
                'acidity_level' => 5.5 - ($i * 0.1),
                'ph_level' => 3.2 + ($i * 0.05),
                'color_rating' => ['excelente', 'bueno', 'aceptable'][$i % 3],
                'aroma_rating' => ['excelente', 'bueno', 'aceptable'][$i % 3],
                'health_status' => ['sano', 'daño_leve', 'daño_moderado'][$i % 3],
                'destination_type' => ['winery', 'direct_sale', 'cooperative'][$i % 3],
                'destination' => "Destino Test {$i}",
                'buyer_name' => $i % 2 === 0 ? "Comprador Test {$i}" : null,
                'price_per_kg' => $pricePerKg,
                'total_value' => $totalWeight * $pricePerKg,
                'status' => 'active',
                'notes' => "Notas de la cosecha #{$i}",
            ]);
        }
        $this->command->info('✅ 5 cosechas creadas');

        // Resumen
        $totalActivities = AgriculturalActivity::forUser($viticulturist->id)
            ->forCampaign($campaign->id)
            ->count();

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('✅ ACTIVIDADES DEL CUADERNO DIGITAL CREADAS');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info("📊 Total de actividades: {$totalActivities}");
        $this->command->info("🦠 Tratamientos fitosanitarios: " . AgriculturalActivity::forUser($viticulturist->id)->ofType('phytosanitary')->count());
        $this->command->info("🌿 Fertilizaciones: " . AgriculturalActivity::forUser($viticulturist->id)->ofType('fertilization')->count());
        $this->command->info("💧 Riegos: " . AgriculturalActivity::forUser($viticulturist->id)->ofType('irrigation')->count());
        $this->command->info("🔧 Labores culturales: " . AgriculturalActivity::forUser($viticulturist->id)->ofType('cultural')->count());
        $this->command->info("👁️ Observaciones: " . AgriculturalActivity::forUser($viticulturist->id)->ofType('observation')->count());
        $this->command->info("🍇 Cosechas: " . AgriculturalActivity::forUser($viticulturist->id)->ofType('harvest')->count());
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('');
        $this->command->info('🎯 Ahora puedes testear la generación de informes oficiales!');
    }
}

