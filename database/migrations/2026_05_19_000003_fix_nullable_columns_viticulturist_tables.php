<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corrección de columnas NOT NULL sin default que los controllers API
 * validan como nullable. Sin este fix, MySQL rechaza INSERTs cuando el
 * cliente móvil no envía el campo.
 *
 * Patrón del bug: migración define NOT NULL, controller dice nullable|...,
 * mobile no envía el campo → SQLSTATE HY000 "Field X doesn't have a default value".
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── phytosanitary_alerts.description ─────────────────────────────────
        Schema::table('phytosanitary_alerts', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });

        // ── water_concessions: water_body, authority, max_volume_m3 ──────────
        Schema::table('water_concessions', function (Blueprint $table) {
            $table->string('water_body')->nullable()->change();
            $table->string('authority')->nullable()->change();
            $table->decimal('max_volume_m3', 12, 3)->nullable()->change();
        });

        // ── agri_insurances.end_date ──────────────────────────────────────────
        Schema::table('agri_insurances', function (Blueprint $table) {
            $table->date('end_date')->nullable()->change();
        });

        // ── phytosanitary_container_returns.collection_point ─────────────────
        Schema::table('phytosanitary_container_returns', function (Blueprint $table) {
            $table->string('collection_point')->nullable()->change();
        });

        // ── field_applicators.ropo_number ─────────────────────────────────────
        Schema::table('field_applicators', function (Blueprint $table) {
            $table->string('ropo_number', 50)->nullable()->change();
        });

        // ── soil_analyses.plot_id ─────────────────────────────────────────────
        Schema::table('soil_analyses', function (Blueprint $table) {
            $table->dropForeign(['plot_id']);
            $table->unsignedBigInteger('plot_id')->nullable()->change();
            $table->foreign('plot_id')->references('id')->on('plots')->onDelete('set null');
        });

        // ── biodiversity_records.plot_id ──────────────────────────────────────
        Schema::table('biodiversity_records', function (Blueprint $table) {
            $table->dropForeign(['plot_id']);
            $table->unsignedBigInteger('plot_id')->nullable()->change();
            $table->foreign('plot_id')->references('id')->on('plots')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('phytosanitary_alerts', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
        });

        Schema::table('water_concessions', function (Blueprint $table) {
            $table->string('water_body')->nullable(false)->change();
            $table->string('authority')->nullable(false)->change();
            $table->decimal('max_volume_m3', 12, 3)->nullable(false)->change();
        });

        Schema::table('agri_insurances', function (Blueprint $table) {
            $table->date('end_date')->nullable(false)->change();
        });

        Schema::table('phytosanitary_container_returns', function (Blueprint $table) {
            $table->string('collection_point')->nullable(false)->change();
        });

        Schema::table('field_applicators', function (Blueprint $table) {
            $table->string('ropo_number', 50)->nullable(false)->change();
        });

        Schema::table('soil_analyses', function (Blueprint $table) {
            $table->dropForeign(['plot_id']);
            $table->unsignedBigInteger('plot_id')->nullable(false)->change();
            $table->foreign('plot_id')->references('id')->on('plots')->onDelete('cascade');
        });

        Schema::table('biodiversity_records', function (Blueprint $table) {
            $table->dropForeign(['plot_id']);
            $table->unsignedBigInteger('plot_id')->nullable(false)->change();
            $table->foreign('plot_id')->references('id')->on('plots')->onDelete('cascade');
        });
    }
};
