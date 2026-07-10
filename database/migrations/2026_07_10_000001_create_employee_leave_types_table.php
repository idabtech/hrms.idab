<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table linking employees to their assigned leave types.
     * Only leave types assigned here will be visible/available to the employee.
     */
    public function up(): void
    {
        if (!Schema::hasTable('employee_leave_types')) {
            Schema::create('employee_leave_types', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('leave_type_id');
                $table->integer('created_by');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_types');
    }
};
