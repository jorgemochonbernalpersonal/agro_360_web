<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('units')->insert([
            'name'       => 'Toneladas',
            'symbol'     => 't',
            'category'   => 'weight',
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('units')->where('symbol', 't')->delete();
    }
};
