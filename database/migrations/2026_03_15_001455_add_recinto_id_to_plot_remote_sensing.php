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
        Schema::table('plot_remote_sensing', function (Blueprint $table) {
            // Per-recinto storage: one row per (plot, recinto, date)
            $table->foreignId('multipart_plot_sigpac_id')
                ->nullable()
                ->after('plot_id')
                ->constrained('multipart_plot_sigpac')
                ->nullOnDelete();

            // Composite index for fast per-recinto queries
            $table->index(
                ['plot_id', 'multipart_plot_sigpac_id', 'image_date'],
                'prs_plot_recinto_date_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('plot_remote_sensing', function (Blueprint $table) {
            $table->dropIndex('prs_plot_recinto_date_idx');
            $table->dropConstrainedForeignId('multipart_plot_sigpac_id');
        });
    }
};
