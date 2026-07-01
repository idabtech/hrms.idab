<?php

namespace App\Http\Controllers;

use App\Models\EmploymentType;
use Illuminate\Http\Request;

class EmploymentTypeController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Employment Type')) {
            $employmenttypes = EmploymentType::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('employmenttype.index', compact('employmenttypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Employment Type')) {
            return view('employmenttype.create');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Employment Type')) {
            // $validator = \Validator::make(
            //     $request->all(),
            //     [
            //         'name' => 'required|in:probation,Full-time,part-time',
            //     ]
            // );
            // if ($validator->fails()) {
            //     $messages = $validator->getMessageBag();

            //     return redirect()->back()->with('error', $messages->first());
            // }

            $employmenttype = new EmploymentType();
            $employmenttype->name = $request->name;
            $employmenttype->created_by = \Auth::user()->creatorId();
            $employmenttype->save();

            return redirect()->route('employmenttype.index')->with('success', __('Employment Type successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(EmploymentType $employmenttype)
    {
        return redirect()->route('employmenttype.index');
    }

    public function edit(EmploymentType $employmenttype)
    {
        if (\Auth::user()->can('Edit Employment Type')) {
            if ($employmenttype->created_by == \Auth::user()->creatorId()) {
                return view('employmenttype.edit', compact('employmenttype'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, EmploymentType $employmenttype)
    {
        if (\Auth::user()->can('Edit Employment Type')) {
            if ($employmenttype->created_by == \Auth::user()->creatorId()) {
                // $validator = \Validator::make(
                //     $request->all(),
                //     [
                //         'name' => 'required|in:probation,Full-time,part-time',
                //     ]
                // );
                // if ($validator->fails()) {
                //     $messages = $validator->getMessageBag();

                //     return redirect()->back()->with('error', $messages->first());
                // }

                $employmenttype->name = $request->name;
                $employmenttype->save();

                return redirect()->route('employmenttype.index')->with('success', __('Employment Type successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(EmploymentType $employmenttype)
    {
        if (\Auth::user()->can('Delete Employment Type')) {
            if ($employmenttype->created_by == \Auth::user()->creatorId()) {
                $employmenttype->delete();

                return redirect()->route('employmenttype.index')->with('success', __('Employment Type successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
