<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Eliminar la tabla si existe  
        Schema::dropIfExists('multiple_plot_sigpac');
        
        // Crear la tabla con tipos correctos (bigint para coincidir con plots, sigpac_code, plot_geometry)
        DB::statement("
            CREATE TABLE `multiple_plot_sigpac` (
                `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `plot_id` bigint(20) UNSIGNED NOT NULL,
                `sigpac_code_id` bigint(20) UNSIGNED NOT NULL,
                `plot_geometry_id` bigint(20) UNSIGNED DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `plot_id` (`plot_id`),
                KEY `sigpac_code_id` (`sigpac_code_id`),
                KEY `plot_geometry_id` (`plot_geometry_id`),
                UNIQUE KEY `unique_plot_sigpac_geometry` (`plot_id`,`sigpac_code_id`,`plot_geometry_id`),
                CONSTRAINT `fk_multiple_plot_sigpac_plot` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_multiple_plot_sigpac_sigpac` FOREIGN KEY (`sigpac_code_id`) REFERENCES `sigpac_code` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_multiple_plot_sigpac_geometry` FOREIGN KEY (`plot_geometry_id`) REFERENCES `plot_geometry` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multiple_plot_sigpac');
    }
};
