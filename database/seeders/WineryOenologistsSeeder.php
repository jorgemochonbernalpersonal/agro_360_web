<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WineryOenologistsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        $oenologists = [
            [
                'user_id'        => self::WINERY_USER_ID,
                'name'           => 'Marcos',
                'surname'        => 'Rodríguez Santana',
                'license_number' => 'OEN-GC-0421',
                'email'          => 'marcos.rodriguez@enologia-canarias.es',
                'phone'          => '+34 928 441 230',
                'active'         => true,
                'notes'          => 'Enólogo jefe. Especialista en variedades autóctonas de Gran Canaria.',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'user_id'        => self::WINERY_USER_ID,
                'name'           => 'Elena',
                'surname'        => 'Castro Medina',
                'license_number' => 'OEN-GC-0389',
                'email'          => 'elena.castro@enologia-canarias.es',
                'phone'          => '+34 928 553 701',
                'active'         => true,
                'notes'          => 'Responsable de análisis y control de calidad. Experta en fermentaciones maloláctcas.',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'user_id'        => self::WINERY_USER_ID,
                'name'           => 'Alejandro',
                'surname'        => 'Pérez Vega',
                'license_number' => 'OEN-GC-0512',
                'email'          => 'alejandro.perez@agrotech.es',
                'phone'          => '+34 629 884 112',
                'active'         => false,
                'notes'          => 'Consultor externo. Colabora en temporada de vendimia.',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ];

        DB::table('oenologists')->insert($oenologists);

        $this->command->info('✅ Enólogos: ' . count($oenologists) . ' registros');
    }

    private function cleanup(): void
    {
        DB::table('oenologists')->where('user_id', self::WINERY_USER_ID)->delete();
    }
}
