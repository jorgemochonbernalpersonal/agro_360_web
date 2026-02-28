<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropForeign(['phytosanitary_product_id']);
            $table->dropColumn([
                'phytosanitary_product_id',
                'nutrient_n',
                'nutrient_p2o5',
                'nutrient_k2o',
                'nutrient_cao',
                'nutrient_mgo',
                'nutrient_so3',
                'organic_matter',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->foreignId('phytosanitary_product_id')->nullable()
                ->after('viticulturist_id')
                ->constrained('phytosanitary_products')
                ->onDelete('set null');
            $table->decimal('nutrient_n', 5, 2)->nullable()->after('expiry_date');
            $table->decimal('nutrient_p2o5', 5, 2)->nullable()->after('nutrient_n');
            $table->decimal('nutrient_k2o', 5, 2)->nullable()->after('nutrient_p2o5');
            $table->decimal('nutrient_cao', 5, 2)->nullable()->after('nutrient_k2o');
            $table->decimal('nutrient_mgo', 5, 2)->nullable()->after('nutrient_cao');
            $table->decimal('nutrient_so3', 5, 2)->nullable()->after('nutrient_mgo');
            $table->decimal('organic_matter', 5, 2)->nullable()->after('nutrient_so3');
        });
    }
};
