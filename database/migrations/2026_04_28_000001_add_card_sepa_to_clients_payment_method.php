<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE clients MODIFY payment_method ENUM('cash','transfer','check','card','sepa','other') NULL");
    }

    public function down(): void
    {
        // Map card/sepa → other before shrinking the enum
        DB::statement("UPDATE clients SET payment_method = 'other' WHERE payment_method IN ('card','sepa')");
        DB::statement("ALTER TABLE clients MODIFY payment_method ENUM('cash','transfer','check','other') NULL");
    }
};
