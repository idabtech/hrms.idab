<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payslip_types')) {
            Schema::table('payslip_types', function (Blueprint $table) {
                if (!Schema::hasColumn('payslip_types', 'salary_basis')) {
                    $table->enum('salary_basis', ['monthly', 'hourly'])->default('monthly')->after('name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payslip_types')) {
            Schema::table('payslip_types', function (Blueprint $table) {
                if (Schema::hasColumn('payslip_types', 'salary_basis')) {
                    $table->dropColumn('salary_basis');
                }
            });
        }
    }
};
