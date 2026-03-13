<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sif_records', function (Blueprint $table) {
            $table->char('huella', 64)->nullable()->after('csv')
                ->comment('SHA-256 del registro — usada para encadenamiento');
        });
    }

    public function down(): void
    {
        Schema::table('sif_records', function (Blueprint $table) {
            $table->dropColumn('huella');
        });
    }
};
