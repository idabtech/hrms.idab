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
        Schema::create('system_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->enum('module_type', ['super_admin', 'company', 'both'])->default('company');
            $table->enum('available_for', ['both', 'individual', 'business'])->default('both');
            $table->foreignId('parent_id')->nullable()->constrained('system_modules')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->boolean('is_always_included')->default(false);
            $table->boolean('is_auto_included')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_modules');
    }
};
