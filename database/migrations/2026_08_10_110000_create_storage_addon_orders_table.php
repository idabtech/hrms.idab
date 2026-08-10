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
        if (!Schema::hasTable('storage_addon_orders')) {
            Schema::create('storage_addon_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_id')->unique();
                $table->bigInteger('user_id');
                $table->bigInteger('storage_addon_id');
                $table->string('addon_title');
                $table->decimal('storage_amount', 15, 2)->comment('Storage size in MB');
                $table->decimal('price', 15, 2)->default(0.00);
                $table->string('price_currency')->default('USD');
                $table->string('payment_type')->default('Manual');
                $table->string('payment_status')->default('Pending')->comment('succeeded, Approved, Pending, Rejected');
                $table->string('receipt')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_addon_orders');
    }
};
