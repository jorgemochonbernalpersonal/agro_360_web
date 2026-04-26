<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make treated_area_ha nullable on post_harvest_treatments.
 *
 * The API controller (NotebookController) already marks this field as
 * 'nullable|numeric|min:0', but the column was created NOT NULL — causing
 * MySQL strict-mode error 1364 when the mobile client omits it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_harvest_treatments', function (Blueprint $table) {
            $table->decimal('treated_area_ha', 10, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('post_harvest_treatments', function (Blueprint $table) {
            $table->decimal('treated_area_ha', 10, 4)->nullable(false)->change();
        });
    }
};
