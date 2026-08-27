<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManagerReviewRequest;
use App\Http\Requests\SelfAssessmentRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\SelfAssessment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SelfAssessmentController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->can('Manage Self Assessment')) {
            $userType = Auth::user()->type;
            $creatorId = Auth::user()->creatorId();

            $query = SelfAssessment::with(['employee', 'ratings', 'tasks']);

            if ($userType == 'employee') {
                $emp = Employee::where('user_id', Auth::id())->first();
                $query->where('employee_id', $emp ? $emp->id : 0);
            } else {
                $query->where('created_by', $creatorId);
            }

            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $selectedMonth = $request->has('month') ? $request->month : date('Y-m');
            if (!empty($selectedMonth)) {
                $query->whereDate('assessment_month', $selectedMonth . '-01');
            }

            $assessments = $query->latest('assessment_month')->get();

            $employees = [];
            if ($userType != 'employee') {
                $employees = Employee::where('created_by', $creatorId)->get()->pluck('name', 'id');
                $employees->prepend(__('All Employees'), '');
            }

            return view('self-assessments.index', compact('assessments', 'employees', 'selectedMonth'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create(Request $request)
    {
        if (Auth::user()->can('Create Self Assessment')) {
            $userType = Auth::user()->type;
            $creatorId = Auth::user()->creatorId();

            $emp = null;
            if ($userType == 'employee') {
                $emp = Employee::with(['designation', 'department'])->where('user_id', Auth::id())->first();
            }

            $empList = Employee::with(['designation', 'department'])->where('created_by', $creatorId)->get();

            $employees = ['' => __('Select Employee')];
            $employeeData = [];
            $managers = ['' => __('Select Reporting Manager')];

            foreach ($empList as $e) {
                $fullName = trim(($e->name ?? '') . ' ' . ($e->last_name ?? ''));
                $nameToUse = $fullName ?: $e->name;

                $employees[$e->id] = $nameToUse;
                $managers[$nameToUse] = $nameToUse;

                $desigName = '';
                if ($e->designation) {
                    $desigName = $e->designation->name ?? '';
                } elseif (!empty($e->designation_id)) {
                    $desigObj = Designation::find($e->designation_id);
                    $desigName = $desigObj ? $desigObj->name : '';
                }

                $deptName = '';
                if ($e->department) {
                    $deptName = $e->department->name ?? '';
                } elseif (!empty($e->department_id)) {
                    $deptObj = Department::find($e->department_id);
                    $deptName = $deptObj ? $deptObj->name : '';
                }

                $employeeData[$e->id] = [
                    'name'        => $nameToUse,
                    'designation' => $desigName,
                    'department'  => $deptName,
                ];
            }

            $designations = ['' => __('Select Designation')] + Designation::where('created_by', $creatorId)->pluck('name', 'name')->toArray();
            $departments  = ['' => __('Select Department')] + Department::where('created_by', $creatorId)->pluck('name', 'name')->toArray();

            $assessment = new SelfAssessment([
                'employee_name'    => $emp ? (trim(($emp->name ?? '') . ' ' . ($emp->last_name ?? '')) ?: $emp->name) : '',
                'designation'      => $emp && $emp->designation ? $emp->designation->name : '',
                'department'       => $emp && $emp->department ? $emp->department->name : '',
                'reporting_manager'=> '',
                'assessment_month' => now()->startOfMonth(),
            ]);

            return view('self-assessments.create', compact('assessment', 'employees', 'employeeData', 'emp', 'designations', 'departments', 'managers'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(SelfAssessmentRequest $request): RedirectResponse
    {
        if (Auth::user()->can('Create Self Assessment')) {
            $userType = Auth::user()->type;
            $creatorId = Auth::user()->creatorId();

            $empId = $request->input('employee_id');
            if ($userType == 'employee' || !$empId) {
                $emp = Employee::where('user_id', Auth::id())->first();
                $empId = $emp ? $emp->id : null;
            }

            if (!$empId && $userType == 'employee') {
                return redirect()->back()->withInput()->with('error', __('Employee profile record not found for this user account.'));
            }

            $assessment = DB::transaction(function () use ($request, $empId, $creatorId) {
                $assessment = SelfAssessment::create($request->headerData() + [
                    'employee_id' => $empId,
                    'user_id'     => Auth::id(),
                    'created_by'  => $creatorId,
                    'status'      => 'draft',
                ]);

                $this->syncTasks($assessment, $request->validated('tasks'));
                $assessment->seedRatingRows();

                return $assessment;
            });

            return redirect()
                ->route('self-assessments.show', $assessment->id)
                ->with('success', __('Self assessment saved as draft successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        if (Auth::user()->can('Bulk Generate Self Assessment') || Auth::user()->can('Create Self Assessment') || in_array(Auth::user()->type, ['company', 'hr', 'super admin'])) {
            $request->validate([
                'employee_ids'     => 'required|array|min:1',
                'assessment_month' => 'required|date_format:Y-m',
                'due_date'         => 'required|date',
            ]);

            $creatorId = Auth::user()->creatorId();
            $empIds    = $request->employee_ids;

            if (in_array('all', $empIds)) {
                $targetEmployees = Employee::with(['designation', 'department'])->where('created_by', $creatorId)->get();
            } else {
                $targetEmployees = Employee::with(['designation', 'department'])->whereIn('id', $empIds)->get();
            }

            if ($targetEmployees->isEmpty()) {
                return redirect()->back()->with('error', __('No valid employees selected for bulk generation.'));
            }

            $monthDate    = $request->assessment_month . '-01';
            $createdCount = 0;
            $skippedCount = 0;

            DB::transaction(function () use ($targetEmployees, $monthDate, $request, $creatorId, &$createdCount, &$skippedCount) {
                foreach ($targetEmployees as $emp) {
                    $exists = SelfAssessment::where('employee_id', $emp->id)
                        ->whereDate('assessment_month', $monthDate)
                        ->exists();

                    if ($exists) {
                        $skippedCount++;
                        continue;
                    }

                    $fullName  = trim(($emp->name ?? '') . ' ' . ($emp->last_name ?? '')) ?: $emp->name;
                    $desigName = $emp->designation ? $emp->designation->name : ($emp->designation_id ? optional(Designation::find($emp->designation_id))->name : '-');
                    $deptName  = $emp->department ? $emp->department->name : ($emp->department_id ? optional(Department::find($emp->department_id))->name : '-');

                    $targetUserId = null;
                    if (!empty($emp->user_id) && $emp->user_id > 0) {
                        if (User::where('id', $emp->user_id)->exists()) {
                            $targetUserId = $emp->user_id;
                        }
                    }

                    $assessment = SelfAssessment::create([
                        'employee_id'       => $emp->id,
                        'user_id'          => $targetUserId,
                        'employee_name'     => $fullName,
                        'designation'       => $desigName ?: '-',
                        'department'        => $deptName ?: '-',
                        'reporting_manager' => '-',
                        'assessment_month'  => $monthDate,
                        'due_date'          => $request->due_date,
                        'status'            => 'draft',
                        'created_by'        => $creatorId,
                    ]);

                    $assessment->seedRatingRows();
                    $createdCount++;
                }
            });

            $msg = __(':count Self Assessment sheet(s) generated successfully.', ['count' => $createdCount]);
            if ($skippedCount > 0) {
                $msg .= ' ' . __(':skipped sheet(s) already existed and were skipped.', ['skipped' => $skippedCount]);
            }

            return redirect()->route('self-assessments.index')->with('success', $msg);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Request $request, $id)
    {
        if (Auth::user()->can('Manage Self Assessment')) {
            $assessment = SelfAssessment::with(['tasks', 'ratings', 'reviewer', 'employee'])->findOrFail($id);
            $this->authorizeAccess($assessment);

            $canReview = (Auth::user()->can('Review Self Assessment') || Auth::user()->type == 'company' || Auth::user()->type == 'super admin') && $assessment->status !== 'draft';

            return view('self-assessments.show', compact('assessment', 'canReview'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(Request $request, $id)
    {
        if (Auth::user()->can('Edit Self Assessment')) {
            $assessment = SelfAssessment::with('tasks')->findOrFail($id);
            $this->authorizeAccess($assessment);

            if (!$assessment->isEditable()) {
                return redirect()->route('self-assessments.show', $assessment->id)->with('error', __('This assessment sheet has been submitted and can no longer be edited.'));
            }

            $userType = Auth::user()->type;
            $creatorId = Auth::user()->creatorId();
            $empList = Employee::with(['designation', 'department'])->where('created_by', $creatorId)->get();

            $employees = ['' => __('Select Employee')];
            $employeeData = [];
            $managers = ['' => __('Select Reporting Manager')];

            foreach ($empList as $e) {
                $fullName = trim(($e->name ?? '') . ' ' . ($e->last_name ?? ''));
                $nameToUse = $fullName ?: $e->name;

                $employees[$e->id] = $nameToUse;
                $managers[$nameToUse] = $nameToUse;

                $desigName = '';
                if ($e->designation) {
                    $desigName = $e->designation->name ?? '';
                } elseif (!empty($e->designation_id)) {
                    $desigObj = Designation::find($e->designation_id);
                    $desigName = $desigObj ? $desigObj->name : '';
                }

                $deptName = '';
                if ($e->department) {
                    $deptName = $e->department->name ?? '';
                } elseif (!empty($e->department_id)) {
                    $deptObj = Department::find($e->department_id);
                    $deptName = $deptObj ? $deptObj->name : '';
                }

                $employeeData[$e->id] = [
                    'name'        => $nameToUse,
                    'designation' => $desigName,
                    'department'  => $deptName,
                ];
            }

            $designations = ['' => __('Select Designation')] + Designation::where('created_by', $creatorId)->pluck('name', 'name')->toArray();
            $departments  = ['' => __('Select Department')] + Department::where('created_by', $creatorId)->pluck('name', 'name')->toArray();

            if (!empty($assessment->designation) && !isset($designations[$assessment->designation])) {
                $designations[$assessment->designation] = $assessment->designation;
            }
            if (!empty($assessment->department) && !isset($departments[$assessment->department])) {
                $departments[$assessment->department] = $assessment->department;
            }
            if (!empty($assessment->reporting_manager) && !isset($managers[$assessment->reporting_manager])) {
                $managers[$assessment->reporting_manager] = $assessment->reporting_manager;
            }

            return view('self-assessments.edit', compact('assessment', 'employees', 'employeeData', 'designations', 'departments', 'managers'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(SelfAssessmentRequest $request, $id): RedirectResponse
    {
        if (Auth::user()->can('Edit Self Assessment')) {
            $assessment = SelfAssessment::findOrFail($id);
            $this->authorizeAccess($assessment);

            if (!$assessment->isEditable()) {
                return redirect()->route('self-assessments.show', $assessment->id)->with('error', __('Submitted sheets cannot be edited.'));
            }

            DB::transaction(function () use ($request, $assessment) {
                $data = $request->headerData();
                if ($request->filled('employee_id')) {
                    $data['employee_id'] = $request->employee_id;
                }
                $assessment->update($data);
                $this->syncTasks($assessment, $request->validated('tasks'));
            });

            return redirect()
                ->route('self-assessments.show', $assessment->id)
                ->with('success', __('Self assessment changes saved successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function submit(Request $request, $id): RedirectResponse
    {
        $assessment = SelfAssessment::findOrFail($id);
        $this->authorizeAccess($assessment);

        if (!$assessment->isEditable()) {
            return redirect()->back()->with('error', __('Assessment is already submitted or reviewed.'));
        }

        $assessment->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()->back()->with('success', __('Self assessment sheet submitted successfully for review.'));
    }

    public function review(ManagerReviewRequest $request, $id): RedirectResponse
    {
        if (Auth::user()->can('Review Self Assessment') || Auth::user()->type == 'company' || Auth::user()->type == 'super admin') {
            $assessment = SelfAssessment::findOrFail($id);

            DB::transaction(function () use ($request, $assessment) {
                $assessment->ratings()->delete();

                foreach ($request->validated('ratings') as $i => $row) {
                    $assessment->ratings()->create([
                        'position'  => $i + 1,
                        'area'      => $row['area'],
                        'score'     => $row['score'] ?? null,
                        'comments'  => $row['comments'] ?? null,
                        'is_custom' => !in_array($row['area'], config('self_assessment.performance_areas', []), true),
                    ]);
                }

                $assessment->update([
                    'status'          => 'reviewed',
                    'reviewed_by'     => Auth::id(),
                    'reviewed_at'     => now(),
                    'manager_summary' => $request->validated('manager_summary'),
                ]);
            });

            return redirect()->back()->with('success', __('Manager review and ratings saved successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id): RedirectResponse
    {
        if (Auth::user()->can('Delete Self Assessment')) {
            $assessment = SelfAssessment::findOrFail($id);
            $this->authorizeAccess($assessment);

            if (!$assessment->isEditable() && Auth::user()->type == 'employee') {
                return redirect()->back()->with('error', __('Submitted assessment cannot be deleted.'));
            }

            $assessment->delete();

            return redirect()
                ->route('self-assessments.index')
                ->with('success', __('Self assessment deleted successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    protected function syncTasks(SelfAssessment $assessment, array $tasks): void
    {
        $assessment->tasks()->delete();

        foreach (array_values($tasks) as $i => $task) {
            $assessment->tasks()->create([
                'position'         => $i + 1,
                'title'            => $task['title'],
                'responsibilities' => $task['responsibilities'] ?? null,
                'status'           => $task['status'],
                'priority'         => $task['priority'],
            ]);
        }
    }

    protected function authorizeAccess(SelfAssessment $assessment): void
    {
        if (Auth::user()->type == 'employee') {
            $emp = Employee::where('user_id', Auth::id())->first();
            if (!$emp || $assessment->employee_id != $emp->id) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            if ($assessment->created_by != Auth::user()->creatorId()) {
                abort(403, 'Unauthorized action.');
            }
        }
    }
}
