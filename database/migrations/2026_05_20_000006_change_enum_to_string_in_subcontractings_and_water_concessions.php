<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // subcontractings.service_type: ENUM → VARCHAR(100)
        DB::statement("ALTER TABLE subcontractings MODIFY COLUMN service_type VARCHAR(100) NOT NULL DEFAULT 'other'");

        // water_concessions.concession_type: ENUM → VARCHAR(100)
        DB::statement('ALTER TABLE water_concessions MODIFY COLUMN concession_type VARCHAR(100) NOT NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE subcontractings MODIFY COLUMN service_type ENUM('harvesting','pruning','treatment','fertilization','irrigation','soil_work','transport','analysis','other') NOT NULL DEFAULT 'other'");
        DB::statement("ALTER TABLE water_concessions MODIFY COLUMN concession_type ENUM('superficial','subterranea','comunidad_regantes','otro') NOT NULL");
    }
};
