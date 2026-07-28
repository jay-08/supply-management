<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number', 30)->unique(); // Goods Received Note
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('delivery_date');
            $table->string('dr_number', 50)->nullable(); // Delivery Receipt from supplier
            $table->string('invoice_number', 50)->nullable();
            $table->enum('status', ['pending', 'partial', 'complete', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->string('attachment')->nullable();
            $table->boolean('inventory_updated')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['purchase_order_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};
