<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('loan_repayments')) {
            Schema::create('loan_repayments', function (Blueprint $table) {
                $table->id();
                $table->integer('employee_id');
                $table->integer('loan_id')->nullable();
                $table->string('title');
                $table->string('type')->default('fixed'); // fixed or percentage
                $table->float('amount', 15, 2);
                $table->integer('created_by');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};
