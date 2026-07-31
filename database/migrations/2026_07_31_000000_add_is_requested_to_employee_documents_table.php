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
        if (!Schema::hasColumn('employee_documents', 'is_requested')) {
            Schema::table('employee_documents', function (Blueprint $table) {
                $table->tinyInteger('is_requested')->default(0)->after('document_value');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('employee_documents', 'is_requested')) {
            Schema::table('employee_documents', function (Blueprint $table) {
                $table->dropColumn('is_requested');
            });
        }
    }
};
