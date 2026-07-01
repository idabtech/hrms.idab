<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make the heavy required columns on `employees` nullable so that the
 * simple external-product registration endpoint can create a minimal
 * employee record without needing branch / department / shift data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('last_name')->nullable()->change();
            $table->string('gender')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->integer('branch_id')->nullable()->change();
            $table->integer('department_id')->nullable()->change();
            $table->integer('subdepartment_id')->nullable()->change();
            $table->integer('designation_id')->nullable()->change();
            $table->integer('shift_id')->nullable()->change();
            $table->integer('created_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('last_name')->nullable(false)->change();
            $table->string('gender')->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
            $table->integer('branch_id')->nullable(false)->change();
            $table->integer('department_id')->nullable(false)->change();
            $table->integer('subdepartment_id')->nullable(false)->change();
            $table->integer('designation_id')->nullable(false)->change();
            $table->integer('shift_id')->nullable(false)->change();
            $table->integer('created_by')->nullable(false)->change();
        });
    }
};
