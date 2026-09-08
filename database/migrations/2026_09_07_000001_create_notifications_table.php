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
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('type')->default('general'); // leave, holiday, event, etc.
                $table->string('title');
                $table->text('message')->nullable();
                $table->string('icon')->nullable();
                $table->string('badge_text')->nullable();
                $table->string('badge_color')->default('primary');
                $table->string('action_url')->nullable();
                $table->json('extra_data')->nullable();
                $table->tinyInteger('is_read')->default(0);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
