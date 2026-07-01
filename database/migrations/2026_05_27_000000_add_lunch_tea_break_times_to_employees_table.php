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
        Schema::table('employees', function (Blueprint $table) {
            // Add lunch break columns
            $table->time('lunch_start')->nullable()->after('company_end_time');
            $table->time('lunch_end')->nullable()->after('lunch_start');
            $table->integer('lunch_minutes')->nullable()->after('lunch_end');

            // Add tea break columns
            $table->time('tea_start')->nullable()->after('lunch_minutes');
            $table->time('tea_end')->nullable()->after('tea_start');
            $table->integer('tea_minutes')->nullable()->after('tea_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'lunch_start',
                'lunch_end',
                'lunch_minutes',
                'tea_start',
                'tea_end',
                'tea_minutes'
            ]);
        });
    }
};
