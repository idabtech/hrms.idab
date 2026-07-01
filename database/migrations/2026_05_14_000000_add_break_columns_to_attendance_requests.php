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
            if (!Schema::hasColumn('attendance_requests', 'total_lunch_time')) {
                $table->string('total_lunch_time')->nullable()->default('00:00:00');
            }
            if (!Schema::hasColumn('attendance_requests', 'total_tea_time')) {
                $table->string('total_tea_time')->nullable()->default('00:00:00');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_requests', function (Blueprint $table) {
            $table->dropColumn(['total_lunch_time', 'total_tea_time']);
        });
    }
};
