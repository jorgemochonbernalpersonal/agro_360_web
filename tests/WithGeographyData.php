<?php

namespace Tests;

use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\Province;

/**
 * Trait para crear datos geográficos básicos necesarios para tests
 */
trait WithGeographyData
{
    /**
     * Crear datos geográficos mínimos para tests
     */
    protected function createGeographyData(): void
    {
        // Solo crear si no existen
        if (AutonomousCommunity::count() === 0) {
            $autonomousCommunity = AutonomousCommunity::create([
                'id' => 1,
                'code' => '01',
                'name' => 'Andalucía',
            ]);

            $province = Province::create([
                'id' => 1,
                'code' => '01',
                'name' => 'Almería',
                'autonomous_community_id' => $autonomousCommunity->id,
            ]);

            Municipality::create([
                'id' => 1,
                'code' => '01001',
                'name' => 'Abla',
                'province_id' => $province->id,
            ]);
        }
    }
}
