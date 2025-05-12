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
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'storage_limit')) {
                $table->float('storage_limit', 20, 2)->default(0.00)->after('description');
            }

            if (!Schema::hasColumn('plans', 'enable_chatgpt')) {
                $table->string('enable_chatgpt')->nullable();
            }

            if (!Schema::hasColumn('plans', 'trial')) {
                $table->integer('trial')->default(0)->after('enable_chatgpt');
            }

            if (!Schema::hasColumn('plans', 'trial_days')) {
                $table->string('trial_days')->nullable()->after('trial');
            }

            if (!Schema::hasColumn('plans', 'is_disable')) {
                $table->integer('is_disable')->default(1)->after('trial_days');
            }

            // Modify price column if it already exists
            if (Schema::hasColumn('plans', 'price')) {
                $table->decimal('price', 30, 2)->nullable()->default(0.0)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['storage_limit', 'enable_chatgpt', 'trial', 'trial_days', 'is_disable']);
            // Do not drop 'price' since we only changed its structure.
        });
    }
};
