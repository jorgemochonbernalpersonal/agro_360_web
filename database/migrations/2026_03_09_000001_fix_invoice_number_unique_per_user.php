<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the global unique index (allows duplicate numbers across users)
            $table->dropUnique(['invoice_number']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            // Add composite unique: invoice_number must be unique per user
            // NULLs are excluded from unique checks, so draft invoices (invoice_number=null) are fine
            $table->unique(['user_id', 'invoice_number'], 'invoices_user_invoice_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_user_invoice_number_unique');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique('invoice_number');
        });
    }
};
