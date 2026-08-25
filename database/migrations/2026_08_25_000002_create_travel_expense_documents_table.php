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
        Schema::create('travel_expense_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('travel_expense_id');
            $table->string('category')->default('document')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->timestamps();

            $table->foreign('travel_expense_id')->references('id')->on('travel_expenses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_expense_documents');
    }
};
