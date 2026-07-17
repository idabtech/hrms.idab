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
        Schema::table('attendance_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_requests', 'decline_leave_type')) {
                $table->enum('decline_leave_type', ['half_day', 'full_day'])->nullable()->after('status')->comment('When declined, specifies if the day should be treated as half-day or full-day leave');
            }
            if (!Schema::hasColumn('attendance_requests', 'decline_leave_type_id')) {
                $table->unsignedBigInteger('decline_leave_type_id')->nullable()->after('decline_leave_type')->comment('The leave type selected by the approver when declining as leave');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_requests', function (Blueprint $table) {
            $table->dropColumn(['decline_leave_type', 'decline_leave_type_id']);
        });
    }
};
