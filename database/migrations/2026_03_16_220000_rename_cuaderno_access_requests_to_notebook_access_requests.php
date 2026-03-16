<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('cuaderno_access_requests', 'notebook_access_requests');
    }

    public function down(): void
    {
        Schema::rename('notebook_access_requests', 'cuaderno_access_requests');
    }
};
