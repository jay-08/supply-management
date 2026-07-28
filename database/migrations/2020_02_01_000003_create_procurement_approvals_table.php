<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('procurement_approvals', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable'); // polymorphic: PR or PO
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->enum('level', ['dept_head', 'supply_officer', 'admin']);
            $table->enum('action', ['approved', 'rejected', 'returned', 'noted']);
            $table->text('remarks')->nullable();
            $table->timestamp('acted_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('procurement_approvals');
    }
};
