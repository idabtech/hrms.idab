<?php

namespace App\Http\Controllers;

use App\Models\Pension;
use App\Models\Employee;
use Illuminate\Http\Request;

class PensionController extends Controller
{
    //
    // public function index(){
    //     $pension = Pension::where('created_by',\Auth::user()->creatorId())->get();
    //     return view('pension.index',compact('pension'));
    // }

    public function show($id){
        $employee = Employee::find($id);

        return view('pension.create',compact('employee'));
    }
  
    public function store(Request $request){
       $request->validate([
        'employee_id' => 'required',
        'title' => 'required',
        'amount' => 'required'
       ]);

       $pension = new Pension();
       $pension->employee_id = $request->employee_id;
       $pension->title = $request->title;
       $pension->amount = $request->amount;
       $pension->created_by = \Auth::user()->creatorId();
       $pension->save();

       return redirect()->back()->with('success', __('Pension  successfully created.'));
    }

    public function edit($pension)
    {
        $pension = Pension::where('id',$pension)->first();
        
        return view('pension.edit', compact('pension'));
    }

    public function update(Request $request){
        $pension = Pension::firstwhere('id', $request->id);
        // dd($pension);
        $pension->id = $request->id;
        $pension->title = $request->title;
        $pension->amount = $request->amount;
        $pension->save();

        return redirect()->back()->with('success','Pension Successfuly updated');
    }

    public function destroy( $id){
        //    dd($id);

        Pension::where('id',$id)->delete();
            return redirect()->back()->with('success', __('Pension successfully deleted.'));
    }
}
