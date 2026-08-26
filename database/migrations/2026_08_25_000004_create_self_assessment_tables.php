<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('self_assessments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('employee_name');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->date('assessment_month');
            $table->string('reporting_manager')->nullable();

            $table->enum('status', ['draft', 'submitted', 'reviewed'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('manager_summary')->nullable();

            $table->integer('created_by')->default(0);

            $table->timestamps();

            $table->unique(['employee_id', 'assessment_month'], 'self_assessments_emp_month_unique');
            $table->index(['assessment_month', 'status']);
            $table->index('created_by');
        });

        Schema::create('assessment_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('self_assessment_id')->constrained('self_assessments')->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('title');
            $table->text('responsibilities')->nullable();
            $table->enum('status', ['completed', 'in_progress', 'pending'])->default('pending');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->timestamps();

            $table->index(['self_assessment_id', 'position']);
        });

        Schema::create('assessment_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('self_assessment_id')->constrained('self_assessments')->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('area');
            $table->unsignedTinyInteger('score')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('is_custom')->default(false);
            $table->timestamps();

            $table->index(['self_assessment_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_ratings');
        Schema::dropIfExists('assessment_tasks');
        Schema::dropIfExists('self_assessments');
    }
};
