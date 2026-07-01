<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'employment_type_id')) {
                $table->unsignedBigInteger('employment_type_id')->nullable()->after('branch_location');
            }
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'employment_type_id')) {
                $table->dropColumn('employment_type_id');
            }
        });
    }
};
