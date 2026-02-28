<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketed_harvests', function (Blueprint $table) {
            $table->foreignId('invoice_id')
                  ->nullable()
                  ->after('active')
                  ->constrained('invoices')
                  ->onDelete('set null');

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::table('marketed_harvests', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropIndex(['invoice_id']);
            $table->dropColumn('invoice_id');
        });
    }
};
