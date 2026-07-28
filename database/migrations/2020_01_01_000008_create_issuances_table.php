<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssuancesTable extends Migration
{
    public function up()
    {
        Schema::create('issuances', function (Blueprint $table) {
            $table->id();
            $table->string('issuance_number')->unique();
            $table->foreignId('supply_request_id')->nullable()->constrained('supply_requests')->nullOnDelete();
            $table->foreignId('issued_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['issued_to', 'issued_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('issuances');
    }
}
