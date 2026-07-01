<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_types')){
            Schema::table('leave_types', function (Blueprint $table) {
                if (!Schema::hasColumn('leave_types', 'is_paid')){
                    // 1 = paid (default), 0 = unpaid
                    $table->boolean('is_paid')->default(1)->after('days');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leave_types')){
            Schema::table('leave_types', function (Blueprint $table) {
                if (Schema::hasColumn('leave_types', 'is_paid')){
                    $table->dropColumn('is_paid');
                }
            });
        }
    }
};
