<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Mail\WarningSend;
use App\Models\Utility;
use App\Models\Warning;
use App\Models\WarningAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WarningController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('Manage Warning'))
        {
            if(Auth::user()->type == 'employee')
            {
                $emp      = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $warnings = Warning::where('warning_by', '=', $emp->id)->with('attachments')->get();
            }
            else
            {
                $warnings = Warning::where('created_by', '=', \Auth::user()->creatorId())->with('attachments')->get();
            }

            return view('warning.index', compact('warnings'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('Create Warning'))
        {
            if(Auth::user()->type == 'employee')
            {
                $user             = \Auth::user();
                $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where('user_id', '!=', $user->id)->get()->pluck('name', 'id');
            }
            else
            {
                $user             = \Auth::user();
                $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            }

            return view('warning.create', compact('employees', 'current_employee'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Warning'))
        {
            if(\Auth::user()->type != 'employee')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'warning_by' => 'required',
                                   ]
                );
            }

            $validator = \Validator::make(
                $request->all(), [
                                   'warning_to' => 'required',
                                   'subject' => 'required',
                                   'warning_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $warning = new Warning();
            if(\Auth::user()->type == 'employee')
            {
                $emp                 = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $warning->warning_by = $emp->id;
            }
            else
            {
                $warning->warning_by = $request->warning_by;
            }
            $warning->warning_to   = $request->warning_to;
            $warning->subject      = $request->subject;
            $warning->warning_date = $request->warning_date;
            $warning->description  = $request->description;
            $warning->created_by   = \Auth::user()->creatorId();
            $warning->save();

            $setings = Utility::settings();

            if($setings['employee_warning'] == 1)
            {
               $employee       = Employee::find($warning->warning_to);
               $uArr = [
                'employee_warning_name'=>$employee->name,
                'warning_subject'=>$request->subject,
                'warning_description'=>$request->description,


             ];
          $resp = Utility::sendEmailTemplate('employee_warning', [$employee->email], $uArr);
          return redirect()->route('warning.index')->with('success', __('Warning  successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
           }

            return redirect()->route('warning.index')->with('success', __('Warning  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Warning $warning)
    {
        return redirect()->route('warning.index');
    }

    public function edit(Warning $warning)
    {
        if(\Auth::user()->can('Edit Warning'))
        {
            $warning_by= $warning->warning_by;
            $user             = \Auth::user();
            $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
            $employees_by       = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employees_to       = Employee::where('created_by', \Auth::user()->creatorId())->where('id','!=', $warning_by)->get()->pluck('name', 'id');
            return view('warning.edit', compact('warning', 'employees_by','employees_to', 'current_employee'));

            // if(Auth::user()->type == 'employee')
            // {
            //     // $user             = \Auth::user();
            //     // $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
            //     $employees_to        = Employee::where('id', '!=',$warning_by)->get()->pluck('name', 'id');
            //     // return view('warning.edit', compact('warning', 'employees_to', 'current_employee'));
            // }
            // else
            // {
            //     // $user             = \Auth::user();
            //     // $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
            //     // $employees_by       = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            //     $employees_to       = Employee::where('created_by', \Auth::user()->creatorId())->where('id','!=', $warning_by)->get()->pluck('name', 'id');
            //     // return view('warning.edit', compact('warning', 'employees_by','employees_to', 'current_employee'));
            // }


        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Warning $warning)
    {
        if(\Auth::user()->can('Edit Warning'))
        {
            if($warning->created_by == \Auth::user()->creatorId())
            {
                if(\Auth::user()->type != 'employee')
                {
                    $validator = \Validator::make(
                        $request->all(), [
                                           'warning_by' => 'required',
                                       ]
                    );
                }

                $validator = \Validator::make(
                    $request->all(), [
                                       'warning_to' => 'required',
                                       'subject' => 'required',
                                       'warning_date' => 'required',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                if(\Auth::user()->type == 'employee')
                {
                    $emp                 = Employee::where('user_id', '=', \Auth::user()->id)->first();
                    $warning->warning_by = $emp->id;
                }
                else
                {
                    $warning->warning_by = $request->warning_by;
                }

                $warning->warning_to   = $request->warning_to;
                $warning->subject      = $request->subject;
                $warning->warning_date = $request->warning_date;
                $warning->description  = $request->description;
                $warning->save();

                return redirect()->route('warning.index')->with('success', __('Warning successfully updated.'));
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

    public function destroy(Warning $warning)
    {
        if(\Auth::user()->can('Delete Warning'))
        {
            if($warning->created_by == \Auth::user()->creatorId())
            {
                $warning->delete();

                return redirect()->route('warning.index')->with('success', __('Warning successfully deleted.'));
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

    // =========================================================================
    // ATTACHMENTS
    // =========================================================================

    public function attachments($id)
    {
        $warning = Warning::find($id);
        if (!$warning || $warning->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $attachments = $warning->attachments;

        return view('warning.attachments', compact('warning', 'attachments'));
    }

    public function attachmentUpload(Request $request, $id)
    {
        $warning = Warning::find($id);
        if (!$warning || $warning->created_by != \Auth::user()->creatorId()) {
            return response()->json(['is_success' => false, 'error' => __('Permission denied.')], 401);
        }

        $request->validate(['file' => 'required']);

        $dir = 'warning_attachments/';
        $fileName = time() . '_' . $request->file->getClientOriginalName();
        $result = Utility::upload_file($request, 'file', $fileName, $dir, []);

        if ($result['flag'] == 1) {
            $maxOrder = WarningAttachment::where('warning_id', $warning->id)->max('sort_order');

            $attachment = WarningAttachment::create([
                'warning_id' => $warning->id,
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
                    'url'       => route('warning.attachment.download', [$warning->id, $attachment->id]),
                    'delete_url'=> route('warning.attachment.delete', [$warning->id, $attachment->id]),
                ],
            ]);
        } else {
            return response()->json(['is_success' => false, 'error' => $result['msg']], 422);
        }
    }

    public function attachmentDownload($id, $attachmentId)
    {
        $warning = Warning::find($id);
        if (!$warning || $warning->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $attachment = WarningAttachment::where('id', $attachmentId)->where('warning_id', $id)->first();
        if (!$attachment) {
            return redirect()->back()->with('error', __('File not found.'));
        }

        $filePath = storage_path('app/public/warning_attachments/' . $attachment->file_path);
        if (file_exists($filePath)) {
            return response()->download($filePath, $attachment->file_name);
        }

        return redirect()->back()->with('error', __('File not found on server.'));
    }

    public function attachmentDelete($id, $attachmentId)
    {
        $warning = Warning::find($id);
        if (!$warning || $warning->created_by != \Auth::user()->creatorId()) {
            return response()->json(['is_success' => false, 'error' => __('Permission denied.')], 401);
        }

        $attachment = WarningAttachment::where('id', $attachmentId)->where('warning_id', $id)->first();
        if (!$attachment) {
            return response()->json(['is_success' => false, 'error' => __('File not found.')], 404);
        }

        $filePath = storage_path('app/public/warning_attachments/' . $attachment->file_path);
        if (file_exists($filePath)) {
            \File::delete($filePath);
        }

        $attachment->delete();

        return response()->json([
            'is_success' => true,
            'message'    => __('Attachment deleted successfully.'),
        ]);
    }
}
