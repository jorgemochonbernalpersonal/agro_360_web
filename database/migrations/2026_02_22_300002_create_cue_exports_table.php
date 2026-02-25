<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cue_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exploitation_id')->constrained('exploitations')->onDelete('cascade');
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');

            $table->integer('campaign_year');
            $table->enum('period_type', ['quarterly', 'annual'])->default('annual');
            $table->date('from_date');
            $table->date('to_date');
            $table->enum('status', ['draft', 'generated', 'sent', 'accepted', 'rejected'])->default('draft');

            $table->json('payload_json')->nullable()->comment('JSON del payload enviado al MAPA');
            $table->json('response_json')->nullable()->comment('Respuesta del MAPA');
            $table->datetime('generated_at')->nullable();
            $table->datetime('sent_at')->nullable();
            $table->datetime('accepted_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('file_path')->nullable()->comment('Ruta del fichero XML/JSON generado');

            $table->timestamps();

            $table->index(['exploitation_id', 'campaign_year']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cue_exports');
    }
};
