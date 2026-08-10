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
        if (!Schema::hasTable('storage_addons')) {
            Schema::create('storage_addons', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->decimal('price', 15, 2)->default(0.00);
                $table->decimal('storage_amount', 15, 2)->comment('Storage size in MB');
                $table->string('addon_type')->default('plan_expiry')->comment('plan_expiry or fixed_duration');
                $table->integer('duration_value')->default(1)->nullable();
                $table->string('duration_type')->default('year')->nullable()->comment('year, month, day');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->bigInteger('created_by')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_addons');
    }
};
