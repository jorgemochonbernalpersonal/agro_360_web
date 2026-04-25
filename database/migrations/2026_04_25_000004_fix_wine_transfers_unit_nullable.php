<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make unit_of_measurement_id nullable on wine_transfers.
 *
 * The API controller (WineProcessController::storeTransfer) never provides
 * this column, causing MySQL strict-mode error 1364 on every transfer insert.
 */
return new class extends Migration
{
    public function up(): void
    {
        $foreignKeys = array_column(Schema::getForeignKeys('wine_transfers'), 'name');

        Schema::table('wine_transfers', function (Blueprint $table) use ($foreignKeys) {
            $fk = collect($foreignKeys)->first(fn($k) => str_contains($k, 'unit_of_measurement_id'));
            if ($fk) {
                $table->dropForeign(['unit_of_measurement_id']);
            }
            $table->unsignedBigInteger('unit_of_measurement_id')->nullable()->change();
            if ($fk) {
                $table->foreign('unit_of_measurement_id')
                    ->references('id')
                    ->on('units_of_measurement')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wine_transfers', function (Blueprint $table) {
            $table->dropForeign(['unit_of_measurement_id']);
            $table->unsignedBigInteger('unit_of_measurement_id')->nullable(false)->change();
            $table->foreign('unit_of_measurement_id')
                ->references('id')
                ->on('units_of_measurement');
        });
    }
};
