<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasColumn('plots', 'winery_id')) {
            Schema::table('plots', function (Blueprint $table) {
                // Attempt to drop foreign key if exists
                try {
                    $table->dropForeign(['winery_id']);
                } catch (\Throwable $e) {
                    // ignore if foreign key doesn't exist
                }

                $table->dropColumn('winery_id');
            });
        }
    }

    public function down()
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->unsignedBigInteger('winery_id')->nullable()->after('id');
            // Recreate FK if necessary (assumes users table)
            $table->foreign('winery_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
