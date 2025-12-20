<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('multiple_plot_sigpac', 'multipart_plot_sigpac');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('multipart_plot_sigpac', 'multiple_plot_sigpac');
    }
};
