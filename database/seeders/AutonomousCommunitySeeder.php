<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AutonomousCommunity;

class AutonomousCommunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $communities = [
            ['code' => '01', 'name' => 'Andalucía'],
            ['code' => '02', 'name' => 'Aragón'],
            ['code' => '03', 'name' => 'Principado de Asturias'],
            ['code' => '04', 'name' => 'Illes Balears'],
            ['code' => '05', 'name' => 'Canarias'],
            ['code' => '06', 'name' => 'Cantabria'],
            ['code' => '07', 'name' => 'Castilla y León'],
            ['code' => '08', 'name' => 'Castilla - La Mancha'],
            ['code' => '09', 'name' => 'Cataluña'],
            ['code' => '10', 'name' => 'Comunitat Valenciana'],
            ['code' => '11', 'name' => 'Extremadura'],
            ['code' => '12', 'name' => 'Galicia'],
            ['code' => '13', 'name' => 'Comunidad de Madrid'],
            ['code' => '14', 'name' => 'Región de Murcia'],
            ['code' => '15', 'name' => 'Comunidad Foral de Navarra'],
            ['code' => '16', 'name' => 'País Vasco'],
            ['code' => '17', 'name' => 'La Rioja'],
            ['code' => '18', 'name' => 'Ceuta'],
            ['code' => '19', 'name' => 'Melilla'],
        ];

        foreach ($communities as $community) {
            AutonomousCommunity::updateOrCreate(
                ['code' => $community['code']],
                ['name' => $community['name']]
            );
        }

        $this->command->info('Comunidades autónomas creadas correctamente.');
    }
}
