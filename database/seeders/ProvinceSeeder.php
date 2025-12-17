<?php

namespace Database\Seeders;

use App\Models\AutonomousCommunity;
use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todas las comunidades autónomas indexadas por código
        $communities = AutonomousCommunity::all()->keyBy('code');

        // Array de provincias: code, name, y el código de la comunidad autónoma a la que pertenecen
        $provinces = [
            ['code' => '04', 'name' => 'Almería', 'community_code' => '01'],
            ['code' => '11', 'name' => 'Cádiz', 'community_code' => '01'],
            ['code' => '14', 'name' => 'Córdoba', 'community_code' => '01'],
            ['code' => '18', 'name' => 'Granada', 'community_code' => '01'],
            ['code' => '21', 'name' => 'Huelva', 'community_code' => '01'],
            ['code' => '23', 'name' => 'Jaén', 'community_code' => '01'],
            ['code' => '29', 'name' => 'Málaga', 'community_code' => '01'],
            ['code' => '41', 'name' => 'Sevilla', 'community_code' => '01'],
            ['code' => '22', 'name' => 'Huesca', 'community_code' => '02'],
            ['code' => '44', 'name' => 'Teruel', 'community_code' => '02'],
            ['code' => '50', 'name' => 'Zaragoza', 'community_code' => '02'],
            ['code' => '33', 'name' => 'Asturias', 'community_code' => '03'],
            ['code' => '07', 'name' => 'Balears, Illes', 'community_code' => '04'],
            ['code' => '35', 'name' => 'Palmas, Las', 'community_code' => '05'],
            ['code' => '38', 'name' => 'Santa Cruz de Tenerife', 'community_code' => '05'],
            ['code' => '39', 'name' => 'Cantabria', 'community_code' => '06'],
            ['code' => '05', 'name' => 'Ávila', 'community_code' => '07'],
            ['code' => '09', 'name' => 'Burgos', 'community_code' => '07'],
            ['code' => '24', 'name' => 'León', 'community_code' => '07'],
            ['code' => '34', 'name' => 'Palencia', 'community_code' => '07'],
            ['code' => '37', 'name' => 'Salamanca', 'community_code' => '07'],
            ['code' => '40', 'name' => 'Segovia', 'community_code' => '07'],
            ['code' => '42', 'name' => 'Soria', 'community_code' => '07'],
            ['code' => '47', 'name' => 'Valladolid', 'community_code' => '07'],
            ['code' => '49', 'name' => 'Zamora', 'community_code' => '07'],
            ['code' => '02', 'name' => 'Albacete', 'community_code' => '08'],
            ['code' => '13', 'name' => 'Ciudad Real', 'community_code' => '08'],
            ['code' => '16', 'name' => 'Cuenca', 'community_code' => '08'],
            ['code' => '19', 'name' => 'Guadalajara', 'community_code' => '08'],
            ['code' => '45', 'name' => 'Toledo', 'community_code' => '08'],
            ['code' => '08', 'name' => 'Barcelona', 'community_code' => '09'],
            ['code' => '17', 'name' => 'Girona', 'community_code' => '09'],
            ['code' => '25', 'name' => 'Lleida', 'community_code' => '09'],
            ['code' => '43', 'name' => 'Tarragona', 'community_code' => '09'],
            ['code' => '03', 'name' => 'Alicante/Alacant', 'community_code' => '10'],
            ['code' => '12', 'name' => 'Castellón/Castelló', 'community_code' => '10'],
            ['code' => '46', 'name' => 'Valencia/València', 'community_code' => '10'],
            ['code' => '06', 'name' => 'Badajoz', 'community_code' => '11'],
            ['code' => '10', 'name' => 'Cáceres', 'community_code' => '11'],
            ['code' => '15', 'name' => 'Coruña, A', 'community_code' => '12'],
            ['code' => '27', 'name' => 'Lugo', 'community_code' => '12'],
            ['code' => '32', 'name' => 'Ourense', 'community_code' => '12'],
            ['code' => '36', 'name' => 'Pontevedra', 'community_code' => '12'],
            ['code' => '28', 'name' => 'Madrid', 'community_code' => '13'],
            ['code' => '30', 'name' => 'Murcia', 'community_code' => '14'],
            ['code' => '31', 'name' => 'Navarra', 'community_code' => '15'],
            ['code' => '01', 'name' => 'Araba/Álava', 'community_code' => '16'],
            ['code' => '48', 'name' => 'Bizkaia', 'community_code' => '16'],
            ['code' => '20', 'name' => 'Gipuzkoa', 'community_code' => '16'],
            ['code' => '26', 'name' => 'Rioja, La', 'community_code' => '17'],
            ['code' => '51', 'name' => 'Ceuta', 'community_code' => '18'],
            ['code' => '52', 'name' => 'Melilla', 'community_code' => '19'],
        ];

        foreach ($provinces as $provinceData) {
            // Buscar la comunidad autónoma por su código para obtener su ID real
            $community = $communities->get($provinceData['community_code']);

            if (!$community) {
                $this->command->warn("No se encontró la comunidad autónoma con código: {$provinceData['community_code']} para la provincia: {$provinceData['name']}");
                continue;
            }

            // Crear o actualizar la provincia usando el ID real de la comunidad autónoma
            Province::updateOrCreate(
                ['code' => $provinceData['code']],
                [
                    'name' => $provinceData['name'],
                    'autonomous_community_id' => $community->id,  // Usar el ID real, no el código
                ]
            );
        }

        $this->command->info('Provincias creadas correctamente.');
    }
}
