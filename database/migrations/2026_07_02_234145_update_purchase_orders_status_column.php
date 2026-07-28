<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Changing ENUM columns requires doctrine/dbal, but using raw SQL is simpler
        // or just alter it to VARCHAR to avoid future issues.
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status VARCHAR(50) DEFAULT 'draft' NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft', 'pending', 'sent', 'partially_delivered', 'delivered', 'cancelled') DEFAULT 'draft' NOT NULL");
    }
};
