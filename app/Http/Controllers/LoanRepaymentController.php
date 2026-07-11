<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Loan;
use App\Models\LoanRepayment;
use Illuminate\Http\Request;

class LoanRepaymentController extends Controller
{
    public function create($id)
    {
        $employee = Employee::find($id);
        $loans = Loan::where('employee_id', $id)->get()->pluck('title', 'id');
        $loans->prepend(__('Select Loan (Optional)'), '');
        $types = LoanRepayment::$types;

        return view('loan_repayment.create', compact('employee', 'loans', 'types'));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Loan')) {
            $validator = \Validator::make($request->all(), [
                'employee_id' => 'required',
                'title'       => 'required',
                'type'        => 'required',
                'amount'      => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            LoanRepayment::create([
                'employee_id' => $request->employee_id,
                'loan_id'     => $request->loan_id ?: null,
                'title'       => $request->title,
                'type'        => $request->type,
                'amount'      => $request->amount,
                'created_by'  => \Auth::user()->creatorId(),
            ]);

            return redirect()->back()->with('success', __('Loan repayment successfully created.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function edit($id)
    {
        $repayment = LoanRepayment::find($id);

        if (\Auth::user()->can('Edit Loan')) {
            if ($repayment->created_by == \Auth::user()->creatorId()) {
                $loans = Loan::where('employee_id', $repayment->employee_id)->get()->pluck('title', 'id');
                $loans->prepend(__('Select Loan (Optional)'), '');
                $types = LoanRepayment::$types;

                return view('loan_repayment.edit', compact('repayment', 'loans', 'types'));
            }
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return response()->json(['error' => __('Permission denied.')], 401);
    }

    public function update(Request $request, $id)
    {
        $repayment = LoanRepayment::find($id);

        if (\Auth::user()->can('Edit Loan')) {
            if ($repayment->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make($request->all(), [
                    'title'  => 'required',
                    'type'   => 'required',
                    'amount' => 'required|numeric|min:0',
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->getMessageBag()->first());
                }

                $repayment->update([
                    'loan_id' => $request->loan_id ?: null,
                    'title'   => $request->title,
                    'type'    => $request->type,
                    'amount'  => $request->amount,
                ]);

                return redirect()->back()->with('success', __('Loan repayment successfully updated.'));
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function destroy($id)
    {
        $repayment = LoanRepayment::find($id);

        if (\Auth::user()->can('Delete Loan')) {
            if ($repayment->created_by == \Auth::user()->creatorId()) {
                $repayment->delete();
                return redirect()->back()->with('success', __('Loan repayment successfully deleted.'));
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }
}
