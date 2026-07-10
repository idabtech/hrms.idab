<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hmrc_rti_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('salary_month', 7); // YYYY-MM
            $table->string('submission_type', 10)->default('FPS'); // FPS, EPS, NVR
            $table->string('status', 20); // submitted, rejected, error, failed
            $table->string('reference')->nullable(); // HMRC submission reference
            $table->text('message')->nullable(); // Response message or error
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'salary_month']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hmrc_rti_submissions');
    }
};
