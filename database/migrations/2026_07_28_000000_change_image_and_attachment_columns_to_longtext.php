<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE inventory_items MODIFY image LONGTEXT NULL');
        DB::statement('ALTER TABLE users MODIFY avatar LONGTEXT NULL');
        DB::statement('ALTER TABLE deliveries MODIFY attachment LONGTEXT NULL');
        DB::statement('ALTER TABLE purchase_orders MODIFY attachment LONGTEXT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE inventory_items MODIFY image VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users MODIFY avatar VARCHAR(255) NULL');
        DB::statement('ALTER TABLE deliveries MODIFY attachment VARCHAR(255) NULL');
        DB::statement('ALTER TABLE purchase_orders MODIFY attachment VARCHAR(255) NULL');
    }
};
