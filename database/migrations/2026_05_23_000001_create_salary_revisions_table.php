<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryRevisionsTable extends Migration
{
    public function up()
    {
        Schema::create('salary_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->float('current_salary', 20, 2);
            $table->float('revised_salary', 20, 2);
            $table->string('revision_type')->default('fixed'); // fixed | percentage
            $table->float('revision_value', 10, 2)->nullable(); // percentage value if type=percentage
            $table->unsignedInteger('cycle_months'); // 3, 6, or 12
            $table->date('effective_from');           // date when this revision kicks in
            $table->string('status')->default('pending'); // pending | applied
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index('effective_from');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_revisions');
    }
}
