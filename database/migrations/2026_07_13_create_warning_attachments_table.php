<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('warning_attachments')) {
            Schema::create('warning_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warning_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('file_name')->nullable();
                $table->string('file_path')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('warning_id')->references('id')->on('warnings')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('warning_attachments');
    }
};
