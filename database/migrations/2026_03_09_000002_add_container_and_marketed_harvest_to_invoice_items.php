<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('container_id')->nullable()->after('harvest_id');
            $table->unsignedBigInteger('marketed_harvest_id')->nullable()->after('container_id');

            $table->foreign('container_id')
                ->references('id')
                ->on('harvest_containers')
                ->nullOnDelete();

            $table->foreign('marketed_harvest_id')
                ->references('id')
                ->on('marketed_harvests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['container_id']);
            $table->dropForeign(['marketed_harvest_id']);
            $table->dropColumn(['container_id', 'marketed_harvest_id']);
        });
    }
};
