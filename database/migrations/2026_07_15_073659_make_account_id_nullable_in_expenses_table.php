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
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'account_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->integer('account_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'account_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->integer('account_id')->nullable(false)->change();
            });
        }
    }
};
