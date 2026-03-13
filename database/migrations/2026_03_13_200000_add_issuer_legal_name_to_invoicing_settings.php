<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoicing_settings', function (Blueprint $table) {
            $table->string('issuer_legal_name', 150)->nullable()
                ->after('user_id')
                ->comment('Nombre/Razón social fiscal del emisor — override de users.name para facturas');
        });
    }

    public function down(): void
    {
        Schema::table('invoicing_settings', function (Blueprint $table) {
            $table->dropColumn('issuer_legal_name');
        });
    }
};
