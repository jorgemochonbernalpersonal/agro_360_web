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
            // LAI (Leaf Area Index)
            $table->decimal('lai', 5, 2)->nullable()->after('evi_mean')
                ->comment('Leaf Area Index - predicts yield');
            
            // Chlorophyll indicators
            $table->decimal('gndvi', 7, 4)->nullable()->after('lai')
                ->comment('Green NDVI - chlorophyll content');
            $table->decimal('ndre', 7, 4)->nullable()->after('gndvi')
                ->comment('Normalized Difference Red Edge');
            $table->decimal('chlorophyll_content', 5, 2)->nullable()->after('ndre')
                ->comment('Relative chlorophyll content (0-100%)');
            
            // Maturity indicators
            $table->decimal('maturity_index', 5, 2)->nullable()->after('chlorophyll_content')
                ->comment('Maturity index (0-100)');
            $table->decimal('predicted_brix', 5, 2)->nullable()->after('maturity_index')
                ->comment('Predicted sugar content (°Brix)');
            $table->integer('days_to_harvest')->nullable()->after('predicted_brix')
                ->comment('Estimated days to optimal harvest');
            
            // Anomaly detection
            $table->boolean('anomaly_detected')->default(false)->after('days_to_harvest')
                ->comment('Whether anomaly was detected');
            $table->string('anomaly_severity', 20)->nullable()->after('anomaly_detected')
                ->comment('Severity: none, low, medium, high, critical');
            $table->string('anomaly_type', 50)->nullable()->after('anomaly_severity')
                ->comment('Type of anomaly detected');
            
            // Add indexes for performance
            $table->index(['anomaly_detected', 'anomaly_severity'], 'idx_anomalies');
            $table->index('maturity_index', 'idx_maturity');
            $table->index('lai', 'idx_lai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plot_remote_sensing', function (Blueprint $table) {
            $table->dropIndex('idx_anomalies');
            $table->dropIndex('idx_maturity');
            $table->dropIndex('idx_lai');
            
            $table->dropColumn([
                'lai',
                'gndvi',
                'ndre',
                'chlorophyll_content',
                'maturity_index',
                'predicted_brix',
                'days_to_harvest',
                'anomaly_detected',
                'anomaly_severity',
                'anomaly_type',
            ]);
        });
    }
};
