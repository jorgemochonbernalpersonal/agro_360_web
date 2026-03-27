<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the viticultor_assignments table.
 *
 * The table was created as a planned replacement for winery_viticulturist but
 * was never implemented — no model or application code ever used it.
 * The existing winery_viticulturist + supervisor_viticulturist pivots cover
 * all relationship logic and are deeply integrated throughout the codebase.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('viticultor_assignments');
    }

    public function down(): void
    {
        // Table intentionally not recreated on rollback.
    }
};
