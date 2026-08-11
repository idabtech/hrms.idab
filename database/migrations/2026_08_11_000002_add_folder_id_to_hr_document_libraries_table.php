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
        if (Schema::hasTable('hr_document_libraries')) {
            Schema::table('hr_document_libraries', function (Blueprint $table) {
                if (!Schema::hasColumn('hr_document_libraries', 'folder_id')) {
                    $table->unsignedBigInteger('folder_id')->nullable()->after('category');
                    $table->foreign('folder_id')->references('id')->on('hr_document_folders')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('hr_document_libraries')) {
            Schema::table('hr_document_libraries', function (Blueprint $table) {
                if (Schema::hasColumn('hr_document_libraries', 'folder_id')) {
                    $table->dropForeign(['folder_id']);
                    $table->dropColumn('folder_id');
                }
            });
        }
    }
};
