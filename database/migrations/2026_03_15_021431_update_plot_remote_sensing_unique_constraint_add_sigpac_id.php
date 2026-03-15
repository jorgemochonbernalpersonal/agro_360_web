<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plot_remote_sensing', function (Blueprint $table) {
            // Drop old unique constraint that didn't include multipart_plot_sigpac_id,
            // which caused duplicate key errors when storing data for multiple SIGPACs
            // of the same plot on the same date.
            $table->dropUnique(['plot_id', 'image_date', 'image_source']);

            // New unique constraint includes sigpac_id so each recinto can have its own row
            $table->unique(
                ['plot_id', 'multipart_plot_sigpac_id', 'image_date', 'image_source'],
                'prs_plot_sigpac_date_source_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('plot_remote_sensing', function (Blueprint $table) {
            $table->dropUnique('prs_plot_sigpac_date_source_unique');
            $table->unique(['plot_id', 'image_date', 'image_source']);
        });
    }
};
