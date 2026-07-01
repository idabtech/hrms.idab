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
        Schema::table('attendance_employees', function (Blueprint $table) {
           $table->time('total_lunch_time')->after('breaks')->default('00:00:00');
           $table->time('total_tea_time')->after('total_lunch_time')->default('00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropColumn(['total_lunch_time', 'total_tea_time']);
        });
    }
};
