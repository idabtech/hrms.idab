<?php

namespace App\Http\Controllers;

use App\Models\SandwichLeaveRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SandwichLeaveRuleController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Leave Type') || Auth::user()->type == 'company' || Auth::user()->type == 'hr') {
            $rules = SandwichLeaveRule::where('created_by', '=', Auth::user()->creatorId())->get();
            return view('sandwich_leave_rule.index', compact('rules'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->can('Create Leave Type') || Auth::user()->type == 'company' || Auth::user()->type == 'hr') {
            return view('sandwich_leave_rule.create');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('Create Leave Type') || Auth::user()->type == 'company' || Auth::user()->type == 'hr') {
            $validator = Validator::make($request->all(), [
                'name'           => 'required|string|max:255',
                'deduction_rate' => 'required|numeric|min:0',
                'rate_type'      => 'required|in:percentage,multiplier',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $rule                 = new SandwichLeaveRule();
            $rule->name           = $request->name;
            $rule->deduction_rate = $request->deduction_rate;
            $rule->rate_type      = $request->rate_type;
            $rule->description   = $request->description;
            $rule->is_active      = ($request->is_active == '1' || $request->is_active == true || $request->is_active == 'on') ? 1 : 0;
            $rule->created_by     = Auth::user()->creatorId();
            $rule->save();

            return redirect()->route('sandwich-leave-rule.index')->with('success', __('Sandwich Leave Rule successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(SandwichLeaveRule $sandwichLeaveRule)
    {
        return redirect()->route('sandwich-leave-rule.index');
    }

    public function edit($id)
    {
        if (Auth::user()->can('Edit Leave Type') || Auth::user()->type == 'company' || Auth::user()->type == 'hr') {
            $sandwichLeaveRule = SandwichLeaveRule::find($id);
            if ($sandwichLeaveRule && $sandwichLeaveRule->created_by == Auth::user()->creatorId()) {
                return view('sandwich_leave_rule.edit', compact('sandwichLeaveRule'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('Edit Leave Type') || Auth::user()->type == 'company' || Auth::user()->type == 'hr') {
            $sandwichLeaveRule = SandwichLeaveRule::find($id);
            if ($sandwichLeaveRule && $sandwichLeaveRule->created_by == Auth::user()->creatorId()) {
                $validator = Validator::make($request->all(), [
                    'name'           => 'required|string|max:255',
                    'deduction_rate' => 'required|numeric|min:0',
                    'rate_type'      => 'required|in:percentage,multiplier',
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->getMessageBag()->first());
                }

                $sandwichLeaveRule->name           = $request->name;
                $sandwichLeaveRule->deduction_rate = $request->deduction_rate;
                $sandwichLeaveRule->rate_type      = $request->rate_type;
                $sandwichLeaveRule->description   = $request->description;
                $sandwichLeaveRule->is_active      = ($request->is_active == '1' || $request->is_active == true || $request->is_active == 'on') ? 1 : 0;
                $sandwichLeaveRule->save();

                return redirect()->route('sandwich-leave-rule.index')->with('success', __('Sandwich Leave Rule successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('Delete Leave Type') || Auth::user()->type == 'company' || Auth::user()->type == 'hr') {
            $sandwichLeaveRule = SandwichLeaveRule::find($id);
            if ($sandwichLeaveRule && $sandwichLeaveRule->created_by == Auth::user()->creatorId()) {
                $sandwichLeaveRule->delete();
                return redirect()->route('sandwich-leave-rule.index')->with('success', __('Sandwich Leave Rule successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
