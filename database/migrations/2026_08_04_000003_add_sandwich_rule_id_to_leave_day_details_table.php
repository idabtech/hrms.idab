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
        Schema::table('leave_day_details', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_day_details', 'sandwich_leave_rule_id')) {
                $table->unsignedBigInteger('sandwich_leave_rule_id')->nullable()->after('day_status');
            }
            if (!Schema::hasColumn('leave_day_details', 'sandwich_deduction_rate')) {
                $table->decimal('sandwich_deduction_rate', 5, 2)->nullable()->after('sandwich_leave_rule_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_day_details', function (Blueprint $table) {
            if (Schema::hasColumn('leave_day_details', 'sandwich_leave_rule_id')) {
                $table->dropColumn('sandwich_leave_rule_id');
            }
            if (Schema::hasColumn('leave_day_details', 'sandwich_deduction_rate')) {
                $table->dropColumn('sandwich_deduction_rate');
            }
        });
    }
};
