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
        Schema::table('crew_members', function (Blueprint $table) {
            $table->string('phytosanitary_license_number')->nullable()->after('assigned_by');
            $table->date('license_expiry_date')->nullable()->after('phytosanitary_license_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crew_members', function (Blueprint $table) {
            $table->dropColumn(['phytosanitary_license_number', 'license_expiry_date']);
        });
    }
};
