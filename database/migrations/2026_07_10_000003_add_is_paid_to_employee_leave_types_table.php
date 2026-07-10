<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add is_paid column so each employee can have a custom paid/unpaid
     * override per leave type (overrides the global leave_types.is_paid).
     */
    public function up(): void
    {
        Schema::table('employee_leave_types', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_leave_types', 'is_paid')) {
                $table->boolean('is_paid')->default(1)->after('total_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_leave_types', function (Blueprint $table) {
            if (Schema::hasColumn('employee_leave_types', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
        });
    }
};
