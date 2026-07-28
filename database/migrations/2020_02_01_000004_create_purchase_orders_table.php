<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->date('po_date');
            $table->date('delivery_date')->nullable();
            $table->string('payment_terms', 100)->nullable();
            $table->string('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', [
                'draft', 'pending', 'sent', 'partially_delivered', 'delivered', 'cancelled'
            ])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'supplier_id']);
            $table->index('po_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
};
