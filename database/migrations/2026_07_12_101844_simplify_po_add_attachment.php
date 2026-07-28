<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SimplifyPoAddAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('attachment')->nullable()->after('notes');
        });

        // Use raw SQL to alter the foreign key column to avoid doctrine/dbal requirement
        DB::statement('ALTER TABLE delivery_items MODIFY purchase_order_item_id bigint unsigned NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });

        DB::statement('ALTER TABLE delivery_items MODIFY purchase_order_item_id bigint unsigned NOT NULL');
    }
}
