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
        Schema::table('travel_expenses', function (Blueprint $table) {
            $table->tinyInteger('document_requested')->default(0)->after('description');
            $table->timestamp('document_requested_at')->nullable()->after('document_requested');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_expenses', function (Blueprint $table) {
            $table->dropColumn(['document_requested', 'document_requested_at']);
        });
    }
};
