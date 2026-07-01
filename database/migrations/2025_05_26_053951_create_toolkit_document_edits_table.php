<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('toolkit_document_edits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('toolkit_document_id');
            $table->unsignedBigInteger('user_id');
            $table->string('edited_file_path');
            $table->string('file_type');
            $table->longText('content')->nullable();
            $table->timestamps();
            $table->foreign('toolkit_document_id')->references('id')->on('toolkit_documents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toolkit_document_edits');
    }
};
