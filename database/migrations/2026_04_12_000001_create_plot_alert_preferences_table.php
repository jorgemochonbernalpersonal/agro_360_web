<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plot_alert_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('ndvi_threshold', 3, 2)->default(0.30);
            $table->boolean('email_enabled')->default(false);
            $table->timestamps();

            $table->unique(['plot_id', 'user_id']);
        });

        // Migrate existing preferences from plots.ndvi_alert_threshold / alert_email_enabled
        DB::table('plots')
            ->whereNotNull('viticulturist_id')
            ->where(function ($q) {
                $q->whereRaw('ndvi_alert_threshold != 0.30')
                  ->orWhere('alert_email_enabled', true);
            })
            ->select('id', 'viticulturist_id', 'ndvi_alert_threshold', 'alert_email_enabled')
            ->orderBy('id')
            ->chunk(500, function ($rows) {
                $inserts = collect($rows)->map(fn ($p) => [
                    'plot_id'        => $p->id,
                    'user_id'        => $p->viticulturist_id,
                    'ndvi_threshold' => $p->ndvi_alert_threshold ?? 0.30,
                    'email_enabled'  => $p->alert_email_enabled ?? false,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ])->all();

                DB::table('plot_alert_preferences')->insertOrIgnore($inserts);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('plot_alert_preferences');
    }
};
