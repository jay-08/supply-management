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
        DB::statement("ALTER TABLE procurement_approvals MODIFY COLUMN level VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE procurement_approvals MODIFY COLUMN action VARCHAR(50) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE procurement_approvals MODIFY COLUMN level ENUM('dept_head', 'supply_officer', 'admin') NOT NULL");
        DB::statement("ALTER TABLE procurement_approvals MODIFY COLUMN action ENUM('approved', 'rejected', 'returned', 'noted') NOT NULL");
    }
};
