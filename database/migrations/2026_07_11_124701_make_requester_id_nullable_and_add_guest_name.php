<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeRequesterIdNullableAndAddGuestName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_requests', function (Blueprint $table) {
            $table->string('guest_name')->nullable()->after('requester_id');
        });
        
        // Use raw SQL to alter the foreign key column to avoid doctrine/dbal requirement
        DB::statement('ALTER TABLE supply_requests MODIFY requester_id bigint unsigned NULL');
    }

    public function down()
    {
        Schema::table('supply_requests', function (Blueprint $table) {
            $table->dropColumn('guest_name');
        });
        
        DB::statement('ALTER TABLE supply_requests MODIFY requester_id bigint unsigned NOT NULL');
    }
}
