<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryRevision;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SalaryRevisionController extends Controller
{
    /**
     * Show the create-revision modal form for a given employee.
     * GET  salary-revision/create/{employee_id}
     */
    public function create($employeeId)
    {
        if (!Auth::user()->can('Create Salary Revision')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $employee = Employee::where('id', $employeeId)
            ->where('created_by', Auth::user()->creatorId())
            ->firstOrFail();

        $cycleOptions  = SalaryRevision::$cycleOptions;
        $revisionTypes = SalaryRevision::$revisionTypes;

        return view('setsalary.salary_revision_create', compact(
            'employee', 'cycleOptions', 'revisionTypes'
        ));
    }

    /**
     * Store a new salary revision.
     * POST  salary-revision
     *
     * After saving, notify the company/admin (creator) via the
     * salary_revision_created email template so they know a revision
     * is pending their approval.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Salary Revision')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = Validator::make($request->all(), [
            'employee_id'    => 'required|integer',
            'revision_type'  => 'required|in:fixed,percentage',
            'revision_value' => 'required|numeric|min:0',
            'cycle_months'   => 'required|in:1,2,3,4,5,6,7,8,9,10,11,12',
            'effective_from' => 'required|date',
            'note'           => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $employee = Employee::where('id', $request->employee_id)
            ->where('created_by', Auth::user()->creatorId())
            ->firstOrFail();

        $revisedSalary = SalaryRevision::calculateRevised(
            $employee->salary,
            $request->revision_type,
            (float) $request->revision_value
        );

        $revision = SalaryRevision::create([
            'employee_id'    => $employee->id,
            'current_salary' => $employee->salary,
            'revised_salary' => $revisedSalary,
            'revision_type'  => $request->revision_type,
            'revision_value' => $request->revision_value,
            'cycle_months'   => $request->cycle_months,
            'effective_from' => $request->effective_from,
            'status'         => 'pending',
            'note'           => $request->note,
            'created_by'     => Auth::user()->creatorId(),
        ]);

        // ── Email: notify the company/admin (creator) that a revision needs approval ──
        $settings = Utility::settings();

        if (!empty($settings['salary_revision_created']) && $settings['salary_revision_created'] == 1) {

            // The creator user is the company/admin who needs to approve
            $creatorUser = User::find(Auth::user()->creatorId());

            if ($creatorUser && !empty($creatorUser->email)) {

                $uArr = [
                    'revision_employee_name'  => $employee->name,
                    'revision_current_salary' => number_format($revision->current_salary, 2),
                    'revision_revised_salary' => number_format($revision->revised_salary, 2),
                    'revision_type'           => ucfirst($revision->revision_type),
                    'revision_value'          => $revision->revision_type === 'percentage'
                                                    ? $revision->revision_value . '%'
                                                    : number_format($revision->revision_value, 2),
                    'revision_effective_from' => $revision->effective_from
                                                    ? \Carbon\Carbon::parse($revision->effective_from)->format('d M Y')
                                                    : '-',
                    'revision_cycle'          => $revision->cycle_months . ' Month(s)',
                    'revision_note'           => $revision->note ?? '-',
                    'revision_approved_by'    => '-',
                ];

                $resp = Utility::sendEmailTemplate(
                    'salary_revision_created',
                    [$creatorUser->id => $creatorUser->email],
                    $uArr
                );
            }
        }

        $successMsg = __('Salary revision scheduled successfully.');

        if (isset($resp) && !empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
            $successMsg .= '<br><span class="text-danger">' . $resp['error'] . '</span>';
        }

        return redirect()->back()->with('success', $successMsg);
    }

    /**
     * Show the edit-revision modal form for a pending revision.
     * GET  salary-revision/{id}/edit
     */
    public function edit($id)
    {
        if (!Auth::user()->can('Edit Salary Revision')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $revision = SalaryRevision::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->where('status', 'pending')
            ->firstOrFail();

        $employee      = Employee::findOrFail($revision->employee_id);
        $cycleOptions  = SalaryRevision::$cycleOptions;
        $revisionTypes = SalaryRevision::$revisionTypes;

        return view('setsalary.salary_revision_edit', compact(
            'revision', 'employee', 'cycleOptions', 'revisionTypes'
        ));
    }

    /**
     * Update a pending revision.
     * PUT  salary-revision/{id}
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Salary Revision')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $revision = SalaryRevision::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->where('status', 'pending')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'revision_type'  => 'required|in:fixed,percentage',
            'revision_value' => 'required|numeric|min:0',
            'cycle_months'   => 'required|in:1,2,3,4,5,6,7,8,9,10,11,12',
            'effective_from' => 'required|date',
            'note'           => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $employee = Employee::findOrFail($revision->employee_id);

        $revisedSalary = SalaryRevision::calculateRevised(
            $employee->salary,
            $request->revision_type,
            (float) $request->revision_value
        );

        $revision->update([
            'current_salary' => $employee->salary,
            'revised_salary' => $revisedSalary,
            'revision_type'  => $request->revision_type,
            'revision_value' => $request->revision_value,
            'cycle_months'   => $request->cycle_months,
            'effective_from' => $request->effective_from,
            'note'           => $request->note,
        ]);

        // ── Email: notify the company/admin (creator) that a revision was updated ──
        $settings = Utility::settings();

        if (!empty($settings['salary_revision_updated']) && $settings['salary_revision_updated'] == 1) {

            $creatorUser = User::find(Auth::user()->creatorId());

            if ($creatorUser && !empty($creatorUser->email)) {

                $uArr = [
                    'revision_employee_name'  => $employee->name,
                    'revision_current_salary' => number_format($revision->current_salary, 2),
                    'revision_revised_salary' => number_format($revision->revised_salary, 2),
                    'revision_type'           => ucfirst($revision->revision_type),
                    'revision_value'          => $revision->revision_type === 'percentage'
                                                    ? $revision->revision_value . '%'
                                                    : number_format($revision->revision_value, 2),
                    'revision_effective_from' => $revision->effective_from
                                                    ? \Carbon\Carbon::parse($revision->effective_from)->format('d M Y')
                                                    : '-',
                    'revision_cycle'          => $revision->cycle_months . ' Month(s)',
                    'revision_note'           => $revision->note ?? '-',
                    'revision_updated_by'     => Auth::user()->name,
                ];

                $resp = Utility::sendEmailTemplate(
                    'salary_revision_updated',
                    [$creatorUser->id => $creatorUser->email],
                    $uArr
                );
            }
        }

        $successMsg = __('Salary revision updated successfully.');

        if (isset($resp) && !empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
            $successMsg .= '<br><span class="text-danger">' . $resp['error'] . '</span>';
        }

        return redirect()->back()->with('success', $successMsg);
    }

    /**
     * Reject (cancel) a pending revision without deleting it.
     * POST  salary-revision/{id}/reject
     */
    public function reject($id)
    {
        if (!Auth::user()->can('Edit Salary Revision')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $allowedTypes = ['company', 'super admin', 'admin'];
        if (!in_array(Auth::user()->type, $allowedTypes)) {
            return redirect()->back()->with('error', __('Only company or admin users can reject salary revisions.'));
        }

        $revision = SalaryRevision::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->where('status', 'pending')
            ->firstOrFail();

        $revision->status = 'rejected';
        $revision->save();

        // ── Email: notify the company/admin (creator) that a revision was rejected ──
        $settings  = Utility::settings();
        $employee  = Employee::findOrFail($revision->employee_id);

        if (!empty($settings['salary_revision_rejected']) && $settings['salary_revision_rejected'] == 1) {

            $creatorUser = User::find($revision->created_by);

            if ($creatorUser && !empty($creatorUser->email)) {

                $uArr = [
                    'revision_employee_name'  => $employee->name,
                    'revision_current_salary' => number_format($revision->current_salary, 2),
                    'revision_revised_salary' => number_format($revision->revised_salary, 2),
                    'revision_type'           => ucfirst($revision->revision_type),
                    'revision_value'          => $revision->revision_type === 'percentage'
                                                    ? $revision->revision_value . '%'
                                                    : number_format($revision->revision_value, 2),
                    'revision_effective_from' => $revision->effective_from
                                                    ? \Carbon\Carbon::parse($revision->effective_from)->format('d M Y')
                                                    : '-',
                    'revision_cycle'          => $revision->cycle_months . ' Month(s)',
                    'revision_note'           => $revision->note ?? '-',
                    'revision_rejected_by'    => Auth::user()->name,
                ];

                $resp = Utility::sendEmailTemplate(
                    'salary_revision_rejected',
                    [$creatorUser->id => $creatorUser->email],
                    $uArr
                );
            }
        }

        $successMsg = __('Salary revision rejected.');

        if (isset($resp) && !empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
            $successMsg .= '<br><span class="text-danger">' . $resp['error'] . '</span>';
        }

        return redirect()->back()->with('success', $successMsg);
    }

    /**
     * Delete a pending revision.
     * DELETE  salary-revision/{id}
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Salary Revision')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $revision = SalaryRevision::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->firstOrFail();

        if ($revision->status === 'applied') {
            return redirect()->back()->with('error', __('Cannot delete an already applied revision.'));
        }

        $revision->delete();

        return redirect()->back()->with('success', __('Salary revision deleted.'));
    }

    /**
     * Approve a pending revision — sets status to 'approved'.
     * The cron command (salary:apply-revisions) will apply the actual salary
     * change on the effective_from date.
     * POST  salary-revision/{id}/approve
     */
    public function approve($id)
    {
        if (!Auth::user()->can('Edit Salary Revision')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // Only company, super admin, or admin-type users may approve
        $allowedTypes = ['company', 'super admin', 'admin'];
        if (!in_array(Auth::user()->type, $allowedTypes)) {
            return redirect()->back()->with('error', __('Only company or admin users can approve salary revisions.'));
        }

        $revision = SalaryRevision::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->where('status', 'pending')
            ->firstOrFail();

        $revision->status = 'approved';
        $revision->save();

        $employee = Employee::findOrFail($revision->employee_id);

        // ── Email: notify that the revision has been approved ──
        $settings = Utility::settings();

        if (!empty($settings['salary_revision_approved']) && $settings['salary_revision_approved'] == 1) {

            $creatorUser = User::find($revision->created_by);

            if ($creatorUser && !empty($creatorUser->email)) {

                $uArr = [
                    'revision_employee_name'  => $employee->name,
                    'revision_current_salary' => number_format($revision->current_salary, 2),
                    'revision_revised_salary' => number_format($revision->revised_salary, 2),
                    'revision_type'           => ucfirst($revision->revision_type),
                    'revision_value'          => $revision->revision_type === 'percentage'
                                                    ? $revision->revision_value . '%'
                                                    : number_format($revision->revision_value, 2),
                    'revision_effective_from' => $revision->effective_from
                                                    ? \Carbon\Carbon::parse($revision->effective_from)->format('d M Y')
                                                    : '-',
                    'revision_cycle'          => $revision->cycle_months . ' Month(s)',
                    'revision_note'           => $revision->note ?? '-',
                    'revision_approved_by'    => Auth::user()->name,
                ];

                $resp = Utility::sendEmailTemplate(
                    'salary_revision_approved',
                    [$creatorUser->id => $creatorUser->email],
                    $uArr
                );
            }
        }

        $successMsg = __('Salary revision approved. The new salary will be applied automatically on the effective date by the scheduled job.');

        if (isset($resp) && !empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
            $successMsg .= '<br><span class="text-danger">' . $resp['error'] . '</span>';
        }

        return redirect()->back()->with('success', $successMsg);
    }
}
