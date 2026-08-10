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
        Schema::create('leave_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_id');
            $table->unsignedBigInteger('requested_by');
            $table->text('request_reason')->nullable();
            $table->text('required_docs')->nullable();
            $table->string('status')->default('pending'); // pending, uploaded, verified
            $table->text('file_paths')->nullable(); // JSON stored array of paths
            $table->text('file_names')->nullable(); // JSON stored array of original file names
            $table->timestamps();

            $table->foreign('leave_id')->references('id')->on('leaves')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_documents');
    }
};
