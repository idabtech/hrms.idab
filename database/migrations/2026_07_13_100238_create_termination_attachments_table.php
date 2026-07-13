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
        if (!Schema::hasTable('termination_attachments')) {
            Schema::create('termination_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('termination_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('file_name')->nullable();
                $table->string('file_path')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
    
                $table->foreign('termination_id')->references('id')->on('terminations')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('termination_attachments');
    }
};
