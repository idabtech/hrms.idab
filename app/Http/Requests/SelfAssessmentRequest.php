<?php

namespace App\Http\Requests;

use App\Models\SelfAssessment;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelfAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $assessmentParam = $this->route('self_assessment') ?? $this->route('id');
        $currentId = 0;
        if ($assessmentParam instanceof SelfAssessment) {
            $currentId = $assessmentParam->id;
        } elseif (is_numeric($assessmentParam)) {
            $currentId = (int) $assessmentParam;
        }

        return [
            'employee_name'     => ['required', 'string', 'max:120'],
            'designation'       => ['nullable', 'string', 'max:120'],
            'department'        => ['nullable', 'string', 'max:120'],
            'reporting_manager' => ['nullable', 'string', 'max:120'],
            'due_date'          => ['nullable', 'date'],
            'assessment_month'  => [
                'required',
                'date_format:Y-m',
                function (string $attribute, mixed $value, Closure $fail) use ($currentId) {
                    $empId = $this->input('employee_id');
                    if (!$empId && \Auth::user()->type == 'employee') {
                        $emp = \App\Models\Employee::where('user_id', \Auth::id())->first();
                        $empId = $emp ? $emp->id : null;
                    }

                    if ($empId) {
                        $clash = SelfAssessment::query()
                            ->where('employee_id', $empId)
                            ->whereDate('assessment_month', $value . '-01')
                            ->when($currentId > 0, fn ($q) => $q->where('id', '!=', $currentId))
                            ->exists();

                        if ($clash) {
                            $fail(__('Self assessment for this employee and month already exists.'));
                        }
                    }
                },
            ],

            'tasks'                    => ['required', 'array', 'min:1', 'max:' . config('self_assessment.max_task_rows', 20)],
            'tasks.*.title'            => ['required', 'string', 'max:180'],
            'tasks.*.responsibilities' => ['nullable', 'string', 'max:2000'],
            'tasks.*.status'           => ['required', Rule::in(array_keys(config('self_assessment.statuses', [])))],
            'tasks.*.priority'         => ['required', Rule::in(array_keys(config('self_assessment.priorities', [])))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $tasks = collect($this->input('tasks', []))
            ->reject(fn ($task) => blank($task['title'] ?? null) && blank($task['responsibilities'] ?? null))
            ->map(function ($task) {
                if (blank($task['status'] ?? null)) {
                    $task['status'] = 'pending';
                }
                if (blank($task['priority'] ?? null)) {
                    $task['priority'] = 'medium';
                }
                return $task;
            })
            ->values()
            ->all();

        $this->merge(['tasks' => $tasks]);
    }

    public function attributes(): array
    {
        return [
            'assessment_month'         => 'assessment month',
            'tasks.*.title'            => 'task / project',
            'tasks.*.responsibilities' => 'key responsibilities',
            'tasks.*.status'           => 'status',
            'tasks.*.priority'         => 'priority',
        ];
    }

    public function messages(): array
    {
        return [
            'tasks.required' => __('Add at least one task before saving.'),
        ];
    }

    public function headerData(): array
    {
        return [
            'employee_name'     => $this->string('employee_name')->toString() ?: 'Employee',
            'designation'       => $this->string('designation')->toString() ?: '-',
            'department'        => $this->string('department')->toString() ?: '-',
            'reporting_manager' => $this->string('reporting_manager')->toString() ?: '-',
            'assessment_month'  => $this->string('assessment_month') . '-01',
            'due_date'          => $this->filled('due_date') ? $this->input('due_date') : null,
        ];
    }
}
