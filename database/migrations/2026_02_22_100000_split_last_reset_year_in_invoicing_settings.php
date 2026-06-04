<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoicing_settings', function (Blueprint $table) {
            $table->integer('invoice_last_reset_year')
                ->default(date('Y'))
                ->after('invoice_year_reset')
                ->comment('Último año de reseteo del contador de facturas');

            $table->integer('delivery_note_last_reset_year')
                ->default(date('Y'))
                ->after('delivery_note_year_reset')
                ->comment('Último año de reseteo del contador de albaranes');
        });

        // Copiar el valor actual de last_reset_year a ambos nuevos campos
        DB::table('invoicing_settings')->update([
            'invoice_last_reset_year' => DB::raw('last_reset_year'),
            'delivery_note_last_reset_year' => DB::raw('last_reset_year'),
        ]);
    }

    public function down(): void
    {
        Schema::table('invoicing_settings', function (Blueprint $table) {
            $table->dropColumn(['invoice_last_reset_year', 'delivery_note_last_reset_year']);
        });
    }
};
