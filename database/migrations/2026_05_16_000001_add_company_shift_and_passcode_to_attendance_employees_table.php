<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyShiftAndPasscodeToAttendanceEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_employees', 'company_shift_time')) {
                $table->string('company_shift_time')->nullable()->after('clock_out');
            }
            if (!Schema::hasColumn('attendance_employees', 'is_manual')) {
                $table->boolean('is_manual')->default(false)->after('company_shift_time');
            }
            if (!Schema::hasColumn('attendance_employees', 'is_manual_by')) {
                $table->string('is_manual_by')->nullable()->after('is_manual');
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
            if (Schema::hasColumn('attendance_employees', 'is_change')) {
                $table->dropColumn('is_change');
            }
            if (Schema::hasColumn('attendance_employees', 'company_shift_time')) {
                $table->dropColumn('company_shift_time');
            }
        });
    }
}
