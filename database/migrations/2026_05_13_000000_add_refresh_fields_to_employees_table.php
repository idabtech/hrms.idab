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
            $table->string('refresh_type')->nullable()->after('lunch_break');
            $table->time('refresh_start')->nullable()->after('refresh_type');
            $table->time('refresh_end')->nullable()->after('refresh_start');
            $table->integer('refresh_minutes')->nullable()->after('refresh_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['refresh_type', 'refresh_start', 'refresh_end', 'refresh_minutes']);
        });
    }
};
