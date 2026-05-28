<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * pests.name era VARCHAR(100), insuficiente para JSON multilocale (5 idiomas ~110 chars).
     * Se convierte a LONGTEXT igual que los otros campos traducibles del catálogo.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `pests` MODIFY COLUMN `name` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `pests` MODIFY COLUMN `name` VARCHAR(100) NOT NULL');
    }
};
