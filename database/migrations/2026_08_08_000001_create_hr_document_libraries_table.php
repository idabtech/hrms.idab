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
        if (!Schema::hasTable('hr_document_libraries')) {
            Schema::create('hr_document_libraries', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('category')->nullable();
                $table->text('description')->nullable();
                $table->string('file_name')->nullable();
                $table->string('file_path')->nullable();
                $table->longText('content')->nullable();
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_document_libraries');
    }
};
