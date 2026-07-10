<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add total_days column so each employee can have a custom leave allowance
     * per leave type (overrides the global leave_types.days).
     */
    public function up(): void
    {
        Schema::table('employee_leave_types', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_leave_types', 'total_days')) {
                $table->decimal('total_days', 5, 1)->default(0)->after('leave_type_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_leave_types', function (Blueprint $table) {
            if (Schema::hasColumn('employee_leave_types', 'total_days')) {
                $table->dropColumn('total_days');
            }
        });
    }
};
