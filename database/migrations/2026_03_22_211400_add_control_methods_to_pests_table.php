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
        Schema::table('pests', function (Blueprint $table) {
            $table->json('control_methods')->nullable()->after('prevention_methods')
                ->comment('Métodos de control IPM ordenados por prioridad PAC: biologico, cultural, fisico, quimico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pests', function (Blueprint $table) {
            $table->dropColumn('control_methods');
        });
    }
};
