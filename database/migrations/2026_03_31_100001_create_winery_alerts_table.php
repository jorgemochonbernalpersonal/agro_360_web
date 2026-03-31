<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winery_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('alert_type', [
                'maintenance',
                'expiry',
                'stock',
                'fermentation',
                'dispute',
                'label',
                'certification',
                'custom',
            ])->default('custom');

            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->string('title', 255);
            $table->text('message')->nullable();

            // Referencia polimórfica opcional (contenedor, vino, lote, etc.)
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('auto_generated')->default(false);
            $table->timestamp('triggered_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'alert_type']);
            $table->index(['user_id', 'severity']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winery_alerts');
    }
};
