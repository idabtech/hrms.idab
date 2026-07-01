<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUkPayrollFieldsToEmployeesTable extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // UK payroll specific fields (added only if not already present)
            if (!Schema::hasColumn('employees', 'ni_number')) {
                $table->string('ni_number')->nullable()->after('tax_payer_id')
                    ->comment('National Insurance Number e.g. JS877742C');
            }
            if (!Schema::hasColumn('employees', 'ni_table_letter')) {
                $table->string('ni_table_letter', 2)->nullable()->default('A')->after('ni_number')
                    ->comment('NI Table Letter e.g. A, B, C, H, M, Z');
            }
            if (!Schema::hasColumn('employees', 'payment_method')) {
                $table->string('payment_method', 20)->nullable()->default('BACS')->after('ni_table_letter')
                    ->comment('Payment method e.g. BACS, CHAPS, Cash, Cheque');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['ni_number', 'ni_table_letter', 'payment_method']);
        });
    }
}
