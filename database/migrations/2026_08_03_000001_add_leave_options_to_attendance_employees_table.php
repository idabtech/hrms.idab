<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_employees', 'leave_pay_type')) {
                $table->string('leave_pay_type', 20)->default('unpaid')->after('company_shift_time');
            }
            if (!Schema::hasColumn('attendance_employees', 'use_leave_balance')) {
                $table->tinyInteger('use_leave_balance')->default(0)->after('leave_pay_type');
            }
            if (!Schema::hasColumn('attendance_employees', 'leave_type_id')) {
                $table->integer('leave_type_id')->nullable()->after('use_leave_balance');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_employees', 'leave_pay_type')) {
                $table->dropColumn('leave_pay_type');
            }
            if (Schema::hasColumn('attendance_employees', 'use_leave_balance')) {
                $table->dropColumn('use_leave_balance');
            }
            if (Schema::hasColumn('attendance_employees', 'leave_type_id')) {
                $table->dropColumn('leave_type_id');
            }
        });
    }
};
