<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Rename cuaderno_* → notebook_* in winery_viticulturist ─────────
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            $table->renameColumn('cuaderno_access',     'notebook_access');
            $table->renameColumn('cuaderno_granted_at', 'notebook_granted_at');
            $table->renameColumn('cuaderno_revoked_at', 'notebook_revoked_at');
        });

        // ── 2. Same rename in supervisor_viticulturist ────────────────────────
        Schema::table('supervisor_viticulturist', function (Blueprint $table) {
            $table->renameColumn('cuaderno_access',     'notebook_access');
            $table->renameColumn('cuaderno_granted_at', 'notebook_granted_at');
            $table->renameColumn('cuaderno_revoked_at', 'notebook_revoked_at');
        });

        // ── 3. Same rename in viticultor_assignments ──────────────────────────
        Schema::table('viticultor_assignments', function (Blueprint $table) {
            $table->renameColumn('cuaderno_access',     'notebook_access');
            $table->renameColumn('cuaderno_granted_at', 'notebook_granted_at');
            $table->renameColumn('cuaderno_revoked_at', 'notebook_revoked_at');
        });

        // ── 4. Ability code: cuaderno_access → notebook_access ────────────────
        DB::table('abilities')
            ->where('code', 'cuaderno_access')
            ->update(['code' => 'notebook_access']);

        // ── 5. Ability module values → English ────────────────────────────────
        DB::table('abilities')->where('module', 'bodega')->update(['module' => 'winery']);
        DB::table('abilities')->where('module', 'regulatorio')->update(['module' => 'regulatory']);
        DB::table('abilities')->where('module', 'viñedo')->update(['module' => 'vineyard']);
        DB::table('abilities')->where('module', 'facturación')->update(['module' => 'invoicing']);

        // ── 6. HarvestByproduct — byproduct_type ENUM rename ─────────────────
        // Step 1: extend ENUM to include both old and new values
        DB::statement("ALTER TABLE harvest_byproducts MODIFY COLUMN byproduct_type
            ENUM('orujo','raspon','lia','otro','pomace','stem','lees','other') NOT NULL");
        // Step 2: migrate data
        DB::table('harvest_byproducts')->where('byproduct_type', 'orujo')->update(['byproduct_type' => 'pomace']);
        DB::table('harvest_byproducts')->where('byproduct_type', 'raspon')->update(['byproduct_type' => 'stem']);
        DB::table('harvest_byproducts')->where('byproduct_type', 'lia')->update(['byproduct_type' => 'lees']);
        DB::table('harvest_byproducts')->where('byproduct_type', 'otro')->update(['byproduct_type' => 'other']);
        // Step 3: shrink ENUM to only new values
        DB::statement("ALTER TABLE harvest_byproducts MODIFY COLUMN byproduct_type
            ENUM('pomace','stem','lees','other') NOT NULL");

        // ── 7. HarvestByproduct — destination_type ENUM rename ────────────────
        DB::statement("ALTER TABLE harvest_byproducts MODIFY COLUMN destination_type
            ENUM('cooperativa','bodega','destileria','compostaje','vertedero_autorizado','otro',
                 'cooperative','winery','distillery','composting','authorized_landfill','other') NOT NULL");
        DB::table('harvest_byproducts')->where('destination_type', 'cooperativa')->update(['destination_type' => 'cooperative']);
        DB::table('harvest_byproducts')->where('destination_type', 'bodega')->update(['destination_type' => 'winery']);
        DB::table('harvest_byproducts')->where('destination_type', 'destileria')->update(['destination_type' => 'distillery']);
        DB::table('harvest_byproducts')->where('destination_type', 'compostaje')->update(['destination_type' => 'composting']);
        DB::table('harvest_byproducts')->where('destination_type', 'vertedero_autorizado')->update(['destination_type' => 'authorized_landfill']);
        DB::table('harvest_byproducts')->where('destination_type', 'otro')->update(['destination_type' => 'other']);
        DB::statement("ALTER TABLE harvest_byproducts MODIFY COLUMN destination_type
            ENUM('cooperative','winery','distillery','composting','authorized_landfill','other') NOT NULL");

        // ── 8. WineSubproduct — type varchar values ───────────────────────────
        DB::table('wine_subproducts')->where('type', 'orujo')->update(['type' => 'pomace']);
        DB::table('wine_subproducts')->where('type', 'lias')->update(['type' => 'lees']);
        DB::table('wine_subproducts')->where('type', 'vinaza')->update(['type' => 'vinasse']);
    }

    public function down(): void
    {
        // ── Reverse cuaderno_* renames ────────────────────────────────────────
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            $table->renameColumn('notebook_access',     'cuaderno_access');
            $table->renameColumn('notebook_granted_at', 'cuaderno_granted_at');
            $table->renameColumn('notebook_revoked_at', 'cuaderno_revoked_at');
        });
        Schema::table('supervisor_viticulturist', function (Blueprint $table) {
            $table->renameColumn('notebook_access',     'cuaderno_access');
            $table->renameColumn('notebook_granted_at', 'cuaderno_granted_at');
            $table->renameColumn('notebook_revoked_at', 'cuaderno_revoked_at');
        });
        Schema::table('viticultor_assignments', function (Blueprint $table) {
            $table->renameColumn('notebook_access',     'cuaderno_access');
            $table->renameColumn('notebook_granted_at', 'cuaderno_granted_at');
            $table->renameColumn('notebook_revoked_at', 'cuaderno_revoked_at');
        });

        // ── Reverse Ability values ────────────────────────────────────────────
        DB::table('abilities')->where('code', 'notebook_access')->update(['code' => 'cuaderno_access']);
        DB::table('abilities')->where('module', 'winery')->update(['module' => 'bodega']);
        DB::table('abilities')->where('module', 'regulatory')->update(['module' => 'regulatorio']);
        DB::table('abilities')->where('module', 'vineyard')->update(['module' => 'viñedo']);
        DB::table('abilities')->where('module', 'invoicing')->update(['module' => 'facturación']);

        // ── Reverse HarvestByproduct ENUMs ────────────────────────────────────
        DB::statement("ALTER TABLE harvest_byproducts MODIFY COLUMN byproduct_type
            ENUM('orujo','raspon','lia','otro','pomace','stem','lees','other') NOT NULL");
        DB::table('harvest_byproducts')->where('byproduct_type', 'pomace')->update(['byproduct_type' => 'orujo']);
        DB::table('harvest_byproducts')->where('byproduct_type', 'stem')->update(['byproduct_type' => 'raspon']);
        DB::table('harvest_byproducts')->where('byproduct_type', 'lees')->update(['byproduct_type' => 'lia']);
        DB::table('harvest_byproducts')->where('byproduct_type', 'other')->update(['byproduct_type' => 'otro']);
        DB::statement("ALTER TABLE harvest_byproducts MODIFY COLUMN byproduct_type
            ENUM('orujo','raspon','lia','otro') NOT NULL");

        DB::statement("ALTER TABLE harvest_byproducts MODIFY COLUMN destination_type
            ENUM('cooperativa','bodega','destileria','compostaje','vertedero_autorizado','otro',
                 'cooperative','winery','distillery','composting','authorized_landfill','other') NOT NULL");
        DB::table('harvest_byproducts')->where('destination_type', 'cooperative')->update(['destination_type' => 'cooperativa']);
        DB::table('harvest_byproducts')->where('destination_type', 'winery')->update(['destination_type' => 'bodega']);
        DB::table('harvest_byproducts')->where('destination_type', 'distillery')->update(['destination_type' => 'destileria']);
        DB::table('harvest_byproducts')->where('destination_type', 'composting')->update(['destination_type' => 'compostaje']);
        DB::table('harvest_byproducts')->where('destination_type', 'authorized_landfill')->update(['destination_type' => 'vertedero_autorizado']);
        DB::table('harvest_byproducts')->where('destination_type', 'other')->update(['destination_type' => 'otro']);
        DB::statement("ALTER TABLE harvest_byproducts MODIFY COLUMN destination_type
            ENUM('cooperativa','bodega','destileria','compostaje','vertedero_autorizado','otro') NOT NULL");

        // ── Reverse WineSubproduct type values ────────────────────────────────
        DB::table('wine_subproducts')->where('type', 'pomace')->update(['type' => 'orujo']);
        DB::table('wine_subproducts')->where('type', 'lees')->update(['type' => 'lias']);
        DB::table('wine_subproducts')->where('type', 'vinasse')->update(['type' => 'vinaza']);
    }
};
