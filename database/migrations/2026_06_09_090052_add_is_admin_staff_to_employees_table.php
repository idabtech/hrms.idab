<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds is_admin_staff flag so we can distinguish auto-created employee
     * records for hr/manager/etc. users from real employee records.
     * Default 0 = real employee, 1 = admin staff (hr, manager, etc.)
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->tinyInteger('is_admin_staff')->default(0)->after('created_by')->comment('1 = admin staff user (hr/manager/etc.), 0 = regular employee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('is_admin_staff');
        });
    }
};
