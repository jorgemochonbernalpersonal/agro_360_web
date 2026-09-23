<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_alert_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->decimal('lat', 9, 6);
            $table->decimal('lng', 9, 6);
            $table->string('token', 64)->unique();
            $table->timestamp('last_mildew_alert_at')->nullable();
            $table->timestamp('last_powdery_alert_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->unique(['email', 'municipality_id']);
            $table->index('unsubscribed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_alert_subscriptions');
    }
};
