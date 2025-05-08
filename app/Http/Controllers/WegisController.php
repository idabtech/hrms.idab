<?php

namespace App\Http\Controllers;

use App\Models\Wegis;
use Illuminate\Http\Request;

class WegisController extends Controller
{
    //
    public function index(){

        $this->data['wegis'] = Wegis::where('created_by', '=', \Auth::user()->creatorId())->get();
        return view('wegis.index',$this->data);
    }

    public function create(){
        $wegisType = Wegis::$wegis_type;
        return view('wegis.create',compact('wegisType'));
    }

    public function save(Request $request){

        $wegis             = new Wegis();
        $wegis->wegis_type = $request->wegis_type;
        $wegis->minimum    = $request->minimum;
        $wegis->da         = $request->da;
        $wegis->created_by = \Auth::user()->creatorId();
        $wegis->save();

        return redirect()->route('wegis.index')->with('success', __('Wegis successfully created.'));
    }

    public function edit($id){
        $wegis = Wegis::where('id',$id)->first();
        $wegisType = Wegis::$wegis_type;
        return view('wegis.edit',compact('wegis','wegisType'));
    }

    public function update(Request $request){

        $wegis             = Wegis::where('id',$request->id)->first();
        $wegis->wegis_type = $request->wegis_type;
        $wegis->minimum    = $request->minimum;
        $wegis->da         = $request->da;
        $wegis->created_by = \Auth::user()->creatorId();
        $wegis->save();

        return redirect()->route('wegis.index')->with('success', __('Wegis successfully updated.'));
    }

    public function delete($id){

        Wegis::where('id',$id)->delete();
        return redirect()->back()->with('success', __('Wegis successfully deleted.'));
    }
}
