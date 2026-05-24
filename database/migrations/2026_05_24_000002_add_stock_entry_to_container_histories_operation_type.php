<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `container_histories` MODIFY COLUMN `operation_type` ENUM(
            'fill',
            'empty',
            'transfer',
            'sale',
            'adjustment',
            'maintenance',
            'wine_transfer_out',
            'wine_transfer_in',
            'wine_transfer_revert_out',
            'wine_transfer_revert_in',
            'wine_loss',
            'wine_loss_revert',
            'bottling',
            'bottling_revert',
            'wine_stock_entry',
            'wine_stock_entry_revert'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `container_histories` MODIFY COLUMN `operation_type` ENUM(
            'fill',
            'empty',
            'transfer',
            'sale',
            'adjustment',
            'maintenance',
            'wine_transfer_out',
            'wine_transfer_in',
            'wine_transfer_revert_out',
            'wine_transfer_revert_in',
            'wine_loss',
            'wine_loss_revert',
            'bottling',
            'bottling_revert'
        ) NOT NULL");
    }
};
