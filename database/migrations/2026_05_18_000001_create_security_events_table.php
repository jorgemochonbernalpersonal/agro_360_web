<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20);               // info, notice, warning, alert, error, critical
            $table->string('event', 100)->index();      // failed_login, access_denied, user_created…
            $table->string('message', 500);             // mensaje legible
            $table->string('ip', 45)->nullable();       // IPv4 / IPv6
            $table->text('user_agent')->nullable();
            $table->string('email', 255)->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->json('context')->nullable();        // resto del contexto
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_events');
    }
};
