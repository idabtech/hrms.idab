<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Mail\TerminationSend;
use App\Models\Termination;
use App\Models\TerminationAttachment;
use App\Models\TerminationType;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TerminationController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('Manage Termination'))
        {
            if(Auth::user()->type == 'employee')
            {
                $emp          = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $terminations = Termination::where('created_by', '=', \Auth::user()->creatorId())->where('employee_id', '=', $emp->id)->get();
            }
            else
            {
                $terminations = Termination::where('created_by', '=', \Auth::user()->creatorId())->with(['employee', 'terminationType'])->get();
            }

            return view('termination.index', compact('terminations'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('Create Termination'))
        {
            $employees        = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $terminationtypes = TerminationType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('termination.create', compact('employees', 'terminationtypes'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Termination'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'termination_type' => 'required',
                                   'notice_date' => 'required',
                                   'termination_date' => 'required|after_or_equal:notice_date',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $termination                   = new Termination();
            $termination->employee_id      = $request->employee_id;
            $termination->termination_type = $request->termination_type;
            $termination->notice_date      = $request->notice_date;
            $termination->termination_date = $request->termination_date;
            $termination->description      = $request->description;
            $termination->created_by       = \Auth::user()->creatorId();
            $termination->save();

            $attachmentPaths = [];
            if ($request->hasFile('attachments')) {
                $sortOrder = 1;
                $dir = 'termination_attachments/';

                foreach ($request->file('attachments') as $index => $file) {
                    $fileName = time() . '_' . $sortOrder . '_' . $file->getClientOriginalName();
                    $originalName = $file->getClientOriginalName();

                    // Use Utility::upload_file — needs a request-like object with the single file
                    $uploadRequest = new Request();
                    $uploadRequest->files->set('file', $file);
                    $result = Utility::upload_file($uploadRequest, 'file', $fileName, $dir, []);

                    if ($result['flag'] == 1) {
                        TerminationAttachment::create([
                            'termination_id' => $termination->id,
                            'user_id'    => \Auth::user()->id,
                            'file_name'  => $originalName,
                            'file_path'  => $fileName,
                            'sort_order' => $sortOrder,
                        ]);

                        $attachmentPaths[] = storage_path('app/public/' . $dir . $fileName);
                    }

                    $sortOrder++;
                }
            }

            $setings = Utility::settings();
            if($setings['employee_termination'] == 1)
            {
                $employee           = Employee::find($termination->employee_id);

            $uArr = [
                'employee_termination_name'=>$employee->name, 
                'notice_date'=>$request->notice_date,
                'termination_date'=>$request->termination_date, 
                'termination_type'=>$request->termination_type,
                'attachments' => $attachmentPaths,
             ];
          $resp = Utility::sendEmailTemplate('employee_termination', [$employee->email], $uArr);
           return redirect()->route('termination.index')->with('success', __('Termination  successfully created.'). ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->route('termination.index')->with('success', __('Termination  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Termination $termination)
    {
        return redirect()->route('termination.index');
    }

    public function edit(Termination $termination)
    {
        if(\Auth::user()->can('Edit Termination'))
        {
            $employees        = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $terminationtypes = TerminationType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            if($termination->created_by == \Auth::user()->creatorId())
            {

                return view('termination.edit', compact('termination', 'employees', 'terminationtypes'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Termination $termination)
    {
        if(\Auth::user()->can('Edit Termination'))
        {
            if($termination->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'employee_id' => 'required',
                                       'termination_type' => 'required',
                                       'notice_date' => 'required',
                                       'termination_date' => 'required',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }


                $termination->employee_id      = $request->employee_id;
                $termination->termination_type = $request->termination_type;
                $termination->notice_date      = $request->notice_date;
                $termination->termination_date = $request->termination_date;
                $termination->description      = $request->description;
                $termination->save();

                return redirect()->route('termination.index')->with('success', __('Termination successfully updated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Termination $termination)
    {
        if(\Auth::user()->can('Delete Termination'))
        {
            if($termination->created_by == \Auth::user()->creatorId())
            {
                $termination->delete();

                return redirect()->route('termination.index')->with('success', __('Termination successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function description($id)
    {
        $termination = Termination::find($id);

        return view('termination.description', compact('termination'));
    }

    // =========================================================================
    // ATTACHMENTS
    // =========================================================================

    public function attachments($id)
    {
        $termination = Termination::find($id);
        if (!$termination || $termination->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $attachments = $termination->attachments;

        return view('termination.attachments', compact('termination', 'attachments'));
    }

    public function attachmentUpload(Request $request, $id)
    {
        $termination = Termination::find($id);
        if (!$termination || $termination->created_by != \Auth::user()->creatorId()) {
            return response()->json(['is_success' => false, 'error' => __('Permission denied.')], 401);
        }

        $request->validate(['file' => 'required']);

        $dir = 'termination_attachments/';
        $fileName = time() . '_' . $request->file->getClientOriginalName();
        $result = Utility::upload_file($request, 'file', $fileName, $dir, []);

        if ($result['flag'] == 1) {
            $maxOrder = TerminationAttachment::where('termination_id', $termination->id)->max('sort_order');

            $attachment = TerminationAttachment::create([
                'termination_id' => $termination->id,
                'user_id'    => \Auth::user()->id,
                'file_name'  => $request->file->getClientOriginalName(),
                'file_path'  => $fileName,
                'sort_order' => ($maxOrder ?? 0) + 1,
            ]);

            return response()->json([
                'is_success' => true,
                'message'    => __('Attachment uploaded successfully.'),
                'attachment' => [
                    'id'        => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'url'       => route('termination.attachment.download', [$termination->id, $attachment->id]),
                    'delete_url'=> route('termination.attachment.delete', [$termination->id, $attachment->id]),
                ],
            ]);
        } else {
            return response()->json(['is_success' => false, 'error' => $result['msg']], 422);
        }
    }

    public function attachmentDownload($id, $attachmentId)
    {
        $termination = Termination::find($id);
        if (!$termination || $termination->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $attachment = TerminationAttachment::where('id', $attachmentId)->where('termination_id', $id)->first();
        if (!$attachment) {
            return redirect()->back()->with('error', __('File not found.'));
        }

        $fileUrl = Utility::get_file('termination_attachments/' . $attachment->file_path);
        return redirect($fileUrl);
    }

    public function attachmentDelete($id, $attachmentId)
    {
        $termination = Termination::find($id);
        if (!$termination || $termination->created_by != \Auth::user()->creatorId()) {
            return response()->json(['is_success' => false, 'error' => __('Permission denied.')], 401);
        }

        $attachment = TerminationAttachment::where('id', $attachmentId)->where('termination_id', $id)->first();
        if (!$attachment) {
            return response()->json(['is_success' => false, 'error' => __('File not found.')], 404);
        }

        // Delete from storage
        $dir = 'termination_attachments/';
        \Storage::disk(Utility::settings()['storage_setting'] ?? 'local')->delete($dir . $attachment->file_path);

        $attachment->delete();

        return response()->json([
            'is_success' => true,
            'message'    => __('Attachment deleted successfully.'),
        ]);
    }
}
