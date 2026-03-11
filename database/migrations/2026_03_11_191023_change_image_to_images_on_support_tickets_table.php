<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->json('images')->nullable()->after('image');
        });

        // Migrar paths existentes al nuevo array JSON
        DB::table('support_tickets')->whereNotNull('image')->get()->each(function ($ticket) {
            DB::table('support_tickets')
                ->where('id', $ticket->id)
                ->update(['images' => json_encode([$ticket->image])]);
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('image')->nullable()->after('images');
        });

        // Restaurar primera imagen al campo string
        DB::table('support_tickets')->whereNotNull('images')->get()->each(function ($ticket) {
            $images = json_decode($ticket->images, true);
            DB::table('support_tickets')
                ->where('id', $ticket->id)
                ->update(['image' => $images[0] ?? null]);
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
