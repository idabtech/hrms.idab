<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHalfDayAndPaidUnpaidToLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds two new columns to the `leaves` table (overall leave-level duration):
     *   - leave_duration  : full_day | half_day  (default = full_day, keeps existing rows valid)
     *   - half_day_period : morning | afternoon   (only for single-day half-day leaves)
     *
     * Creates `leave_day_details` to store per-day breakdown for multi-day leaves:
     *   - One row per calendar day in the leave range
     *   - day_duration  : full_day | half_day   per that specific day
     *   - half_day_period : morning | afternoon  per that specific day (nullable)
     *   - day_status    : paid | unpaid          per that specific day
     */
    public function up()
    {
        // ── 1. Leave-level columns (used for single-day / pure half-day leaves)
        if (Schema::hasTable('leaves')){
            Schema::table('leaves', function (Blueprint $table) {
                if (!Schema::hasColumn('leaves', 'leave_duration')){
                    $table->enum('leave_duration', ['full_day', 'half_day'])->default('full_day')->after('total_leave_days');
                }
                if (!Schema::hasColumn('leaves', 'half_day_period')){
                    $table->enum('half_day_period', ['morning', 'afternoon'])->nullable()->after('leave_duration');
                }
            });
        }

        // ── 2. Per-day breakdown table ────────────────────────────────────────
        if (!Schema::hasTable('leave_day_details')){
            Schema::create('leave_day_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('leave_id')->nullable();
                $table->date('date');
                $table->enum('day_duration', ['full_day', 'half_day'])->default('full_day');
                $table->enum('half_day_period', ['morning', 'afternoon'])->nullable();
                $table->enum('day_status', ['paid', 'unpaid'])->default('unpaid');
                $table->timestamps();

                $table->foreign('leave_id')->references('id')->on('leaves')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('leave_day_details');

        Schema::table('leaves', function (Blueprint $table) {
            if (Schema::hasColumn('leaves', 'leave_duration') && Schema::hasColumn('leaves', 'half_day_period')){
                $table->dropColumn(['leave_duration', 'half_day_period']);
            }
        });
    }
}
