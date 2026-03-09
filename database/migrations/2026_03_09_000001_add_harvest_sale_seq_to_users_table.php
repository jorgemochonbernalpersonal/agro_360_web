<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('harvest_sale_seq')->default(0)->after('grape_purchase_seq')
                ->comment('Contador para numeración de facturas de venta de uva (viticultor)');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('harvest_sale_seq');
        });
    }
};
