<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\Municipality;
use App\Models\AutonomousCommunity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpainGeographySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Spain geography data...');

        // Comunidades Autónomas principales
        $communities = [
            ['id' => 1, 'name' => 'Andalucía', 'code' => 'AN'],
            ['id' => 2, 'name' => 'Aragón', 'code' => 'AR'],
            ['id' => 3, 'name' => 'Castilla y León', 'code' => 'CL'],
            ['id' => 4, 'name' => 'Castilla-La Mancha', 'code' => 'CM'],
            ['id' => 5, 'name' => 'Cataluña', 'code' => 'CT'],
            ['id' => 6, 'name' => 'Comunidad Valenciana', 'code' => 'VC'],
            ['id' => 7, 'name' => 'Extremadura', 'code' => 'EX'],
            ['id' => 8, 'name' => 'Galicia', 'code' => 'GA'],
            ['id' => 9, 'name' => 'Madrid', 'code' => 'MD'],
            ['id' => 10, 'name' => 'Murcia', 'code' => 'MC'],
            ['id' => 11, 'name' => 'Navarra', 'code' => 'NC'],
            ['id' => 12, 'name' => 'País Vasco', 'code' => 'PV'],
            ['id' => 13, 'name' => 'La Rioja', 'code' => 'RI'],
        ];

        foreach ($communities as $community) {
            AutonomousCommunity::updateOrCreate(
                ['id' => $community['id']],
                $community
            );
        }

        $this->command->info('✓ Created ' . count($communities) . ' autonomous communities');

        // Provincias vitivinícolas principales
        $provinces = [
            // La Rioja
            ['name' => 'La Rioja', 'code' => '26', 'autonomous_community_id' => 13],
            
            // Castilla y León
            ['name' => 'Valladolid', 'code' => '47', 'autonomous_community_id' => 3],
            ['name' => 'Burgos', 'code' => '09', 'autonomous_community_id' => 3],
            ['name' => 'Zamora', 'code' => '49', 'autonomous_community_id' => 3],
            
            // Castilla-La Mancha
            ['name' => 'Toledo', 'code' => '45', 'autonomous_community_id' => 4],
            ['name' => 'Ciudad Real', 'code' => '13', 'autonomous_community_id' => 4],
            ['name' => 'Cuenca', 'code' => '16', 'autonomous_community_id' => 4],
            
            // Cataluña
            ['name' => 'Barcelona', 'code' => '08', 'autonomous_community_id' => 5],
            ['name' => 'Tarragona', 'code' => '43', 'autonomous_community_id' => 5],
            
            // Andalucía
            ['name' => 'Cádiz', 'code' => '11', 'autonomous_community_id' => 1],
            ['name' => 'Málaga', 'code' => '29', 'autonomous_community_id' => 1],
            ['name' => 'Córdoba', 'code' => '14', 'autonomous_community_id' => 1],
            
            // Comunidad Valenciana
            ['name' => 'Valencia', 'code' => '46', 'autonomous_community_id' => 6],
            ['name' => 'Alicante', 'code' => '03', 'autonomous_community_id' => 6],
        ];

        foreach ($provinces as $index => $province) {
            Province::updateOrCreate(
                ['code' => $province['code']],
                array_merge($province, ['id' => $index + 1])
            );
        }

        $this->command->info('✓ Created ' . count($provinces) . ' provinces');

        // Municipios de ejemplo (principales zonas vitivinícolas)
        $municipalities = [
            // La Rioja
            ['name' => 'Logroño', 'province_id' => 1, 'code' => '26089'],
            ['name' => 'Haro', 'province_id' => 1, 'code' => '26073'],
            ['name' => 'Fuenmayor', 'province_id' => 1, 'code' => '26068'],
            
            // Valladolid (Ribera del Duero)
            ['name' => 'Valladolid', 'province_id' => 2, 'code' => '47186'],
            ['name' => 'Peñafiel', 'province_id' => 2, 'code' => '47121'],
            ['name' => 'Aranda de Duero', 'province_id' => 3, 'code' => '09018'],
            
            // La Mancha
            ['name' => 'Tomelloso', 'province_id' => 6, 'code' => '13080'],
            ['name' => 'Valdepeñas', 'province_id' => 6, 'code' => '13086'],
            
            // Penedès
            ['name' => 'Vilafranca del Penedès', 'province_id' => 9, 'code' => '08305'],
        ];

        foreach ($municipalities as $index => $municipality) {
            Municipality::updateOrCreate(
                ['code' => $municipality['code']],
                array_merge($municipality, ['id' => $index + 1])
            );
        }

        $this->command->info('✓ Created ' . count($municipalities) . ' municipalities');

        $this->command->newLine();
        $this->command->info('Spain geography data seeded successfully!');
    }
}
