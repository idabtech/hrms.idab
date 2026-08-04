<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sandwich_leave_rules')) {
            Schema::create('sandwich_leave_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('deduction_rate', 5, 2)->default(1.00);
                $table->enum('rate_type', ['percentage', 'multiplier'])->default('percentage');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(1);
                $table->integer('created_by');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sandwich_leave_rules');
    }
};
