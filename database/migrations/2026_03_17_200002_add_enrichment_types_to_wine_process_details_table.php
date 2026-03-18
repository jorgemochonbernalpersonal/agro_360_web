<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MariaDB: modify enum añadiendo los nuevos valores
        DB::statement("
            ALTER TABLE wine_process_details
            MODIFY COLUMN process_type ENUM(
                'destemming_crushing',
                'pressing',
                'settling',
                'fermentation',
                'maceration',
                'malolactic',
                'aging',
                'racking',
                'blending',
                'fining',
                'filtration',
                'cold_stabilization',
                'bottling',
                'enrichment',
                'concentration',
                'acidification',
                'desacidification',
                'sulfitation',
                'other'
            ) NOT NULL DEFAULT 'fermentation'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE wine_process_details
            MODIFY COLUMN process_type ENUM(
                'destemming_crushing',
                'pressing',
                'settling',
                'fermentation',
                'maceration',
                'malolactic',
                'aging',
                'racking',
                'blending',
                'fining',
                'filtration',
                'cold_stabilization',
                'bottling',
                'other'
            ) NOT NULL DEFAULT 'fermentation'
        ");
    }
};
