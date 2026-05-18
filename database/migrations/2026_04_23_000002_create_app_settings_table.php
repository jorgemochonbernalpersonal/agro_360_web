<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Default values
        DB::table('app_settings')->insert([
            ['key' => 'registration_open',  'value' => '1',                  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'maintenance_mode',   'value' => '0',                  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'support_email',      'value' => 'info@agro365.es',    'created_at' => now(), 'updated_at' => now()],
            ['key' => 'beta_end_date',      'value' => '2026-06-30',         'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
