<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->unsignedBigInteger('employee_id')->nullable()->after('id');
            $table->date('date')->nullable()->after('employee_id');
            $table->enum('type', [
                'regular','overtime','holiday','night',
                'weekend','emergency','training','leave',
            ])->default('regular')->after('company_end_time');
            $table->string('notes')->nullable()->after('type');
            $table->boolean('is_deleted')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['employee_id', 'date', 'type', 'notes']);
            $table->string('name')->nullable(false)->change();
        });
    }
};
