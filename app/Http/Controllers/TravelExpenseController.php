<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TravelExpense;
use App\Models\TravelExpenseDocument;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class TravelExpenseController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->can('Manage Travel Expense')) {
            if (Auth::user()->type == 'employee') {
                $emp = Employee::where('user_id', Auth::user()->id)->first();
                $query = TravelExpense::where('employee_id', $emp ? $emp->id : 0)
                    ->with(['employee', 'documents']);
            } else {
                $query = TravelExpense::where('created_by', Auth::user()->creatorId())
                    ->with(['employee', 'documents']);

                if ($request->filled('employee_id')) {
                    $query->where('employee_id', $request->employee_id);
                }
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('start_date')) {
                $query->where('start_date', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->where('end_date', '<=', $request->end_date);
            }

            $travelExpenses = $query->orderBy('id', 'desc')->get();

            $employeesList = Employee::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
            $employees = ['' => __('All')] + $employeesList;

            $types = [
                '' => __('All'),
                'travel' => __('Travel'),
                'voucher' => __('Voucher'),
            ];

            return view('travel_expense.index', compact('travelExpenses', 'employees', 'types'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->can('Create Travel Expense')) {
            if (Auth::user()->type == 'employee') {
                $user = Auth::user();
                $employees = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
            } else {
                $employees = Employee::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            }

            $types = [
                'travel' => __('Travel'),
                'voucher' => __('Voucher'),
            ];

            return view('travel_expense.create', compact('employees', 'types'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('Create Travel Expense')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'type' => 'required|in:travel,voucher',
                    'title' => 'required|string|max:255',
                    'amount' => 'required|numeric|min:0',
                    'start_date' => 'required|date',
                    'end_date' => 'required|date|after_or_equal:start_date',
                    'description' => 'nullable|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $travelExpense = new TravelExpense();
            $travelExpense->employee_id = $request->employee_id;
            $travelExpense->type = $request->type;
            $travelExpense->title = $request->title;
            $travelExpense->amount = $request->amount;
            $travelExpense->start_date = $request->start_date;
            $travelExpense->end_date = $request->end_date;
            $travelExpense->description = $request->description;
            $travelExpense->created_by = Auth::user()->creatorId();
            $travelExpense->save();

            $dir = 'travel_expenses/';

            // Process uploaded files (Bills & Documents)
            $uploadedFiles = array_merge(
                $request->hasFile('documents') ? $request->file('documents') : [],
                $request->hasFile('bills') ? $request->file('bills') : []
            );

            foreach ($uploadedFiles as $index => $file) {
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . ($index + 1) . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $originalName);

                $uploadRequest = new Request();
                $uploadRequest->files->set('file', $file);
                $result = Utility::upload_file($uploadRequest, 'file', $fileName, $dir, []);

                if (isset($result['flag']) && $result['flag'] == 1) {
                    TravelExpenseDocument::create([
                        'travel_expense_id' => $travelExpense->id,
                        'category' => 'document',
                        'file_name' => $originalName,
                        'file_path' => $fileName,
                    ]);
                }
            }

            return redirect()->route('travel-expenses.index')->with('success', __('Travel Expense / Voucher created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id)
    {
        if (Auth::user()->can('Manage Travel Expense')) {
            $travelExpense = TravelExpense::with(['employee', 'documents'])->find($id);

            if (!$travelExpense) {
                return response()->json(['error' => __('Record not found.')], 444);
            }

            return view('travel_expense.show', compact('travelExpense'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function edit($id)
    {
        if (Auth::user()->can('Edit Travel Expense')) {
            $travelExpense = TravelExpense::with(['documents'])->find($id);

            if (!$travelExpense) {
                return response()->json(['error' => __('Record not found.')], 444);
            }

            if (Auth::user()->type == 'employee') {
                $user = Auth::user();
                $employees = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
            } else {
                $employees = Employee::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            }

            $types = [
                'travel' => __('Travel'),
                'voucher' => __('Voucher'),
            ];

            return view('travel_expense.edit', compact('travelExpense', 'employees', 'types'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('Edit Travel Expense')) {
            $travelExpense = TravelExpense::find($id);

            if (!$travelExpense) {
                return redirect()->back()->with('error', __('Record not found.'));
            }

            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'type' => 'required|in:travel,voucher',
                    'title' => 'required|string|max:255',
                    'amount' => 'required|numeric|min:0',
                    'start_date' => 'required|date',
                    'end_date' => 'required|date|after_or_equal:start_date',
                    'description' => 'nullable|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $travelExpense->employee_id = $request->employee_id;
            $travelExpense->type = $request->type;
            $travelExpense->title = $request->title;
            $travelExpense->amount = $request->amount;
            $travelExpense->start_date = $request->start_date;
            $travelExpense->end_date = $request->end_date;
            $travelExpense->description = $request->description;
            $travelExpense->save();

            $dir = 'travel_expenses/';

            // Process uploaded files (Bills & Documents)
            $uploadedFiles = array_merge(
                $request->hasFile('documents') ? $request->file('documents') : [],
                $request->hasFile('bills') ? $request->file('bills') : []
            );

            foreach ($uploadedFiles as $index => $file) {
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . ($index + 1) . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $originalName);

                $uploadRequest = new Request();
                $uploadRequest->files->set('file', $file);
                $result = Utility::upload_file($uploadRequest, 'file', $fileName, $dir, []);

                if (isset($result['flag']) && $result['flag'] == 1) {
                    TravelExpenseDocument::create([
                        'travel_expense_id' => $travelExpense->id,
                        'category' => 'document',
                        'file_name' => $originalName,
                        'file_path' => $fileName,
                    ]);
                }
            }

            return redirect()->route('travel-expenses.index')->with('success', __('Travel Expense / Voucher updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('Delete Travel Expense')) {
            $travelExpense = TravelExpense::with('documents')->find($id);

            if ($travelExpense) {
                $dir = 'travel_expenses/';
                foreach ($travelExpense->documents as $doc) {
                    $filePath = storage_path('app/public/' . $dir . $doc->file_path);
                    if (File::exists($filePath)) {
                        File::delete($filePath);
                    }
                    $doc->delete();
                }

                $travelExpense->delete();
                return redirect()->route('travel-expenses.index')->with('success', __('Travel Expense / Voucher deleted successfully.'));
            } else {
                return redirect()->back()->with('error', __('Record not found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroyDocument($id)
    {
        $document = TravelExpenseDocument::with('travelExpense')->find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => __('Document not found.')
            ], 404);
        }

        $canDelete = false;
        if (Auth::user()->can('Edit Travel Expense') || Auth::user()->can('Manage Travel Expense') || Auth::user()->type == 'company' || Auth::user()->type == 'super admin') {
            $canDelete = true;
        } else if (Auth::user()->type == 'employee') {
            $emp = Employee::where('user_id', Auth::user()->id)->first();
            if ($emp && $document->travelExpense && $document->travelExpense->employee_id == $emp->id) {
                $canDelete = true;
            }
        }

        if ($canDelete) {
            $dir = 'travel_expenses/';
            $filePath = storage_path('app/public/' . $dir . $document->file_path);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => __('Document deleted successfully.')
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => __('Permission denied.')
            ], 401);
        }
    }

    public function requestDocument($id)
    {
        if (Auth::user()->type == 'company' || Auth::user()->type == 'hr' || Auth::user()->type == 'HR' || Auth::user()->type == 'super admin') {
            $travelExpense = TravelExpense::find($id);
            if ($travelExpense) {
                $travelExpense->document_requested = 1;
                $travelExpense->document_requested_at = now();
                $travelExpense->save();

                return redirect()->back()->with('success', __('Document request sent to employee successfully.'));
            }
            return redirect()->back()->with('error', __('Travel Expense / Voucher not found.'));
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function uploadDocumentModal($id)
    {
        $travelExpense = TravelExpense::with(['employee', 'documents'])->find($id);

        if ($travelExpense) {
            if (Auth::user()->type == 'employee') {
                $emp = Employee::where('user_id', Auth::user()->id)->first();
                if (!$emp || $travelExpense->employee_id != $emp->id) {
                    return response()->json(['error' => __('Permission denied.')], 401);
                }
            }

            return view('travel_expense.upload_document', compact('travelExpense'));
        }

        return response()->json(['error' => __('Record not found.')], 404);
    }

    public function storeRequestedDocument(Request $request, $id)
    {
        $travelExpense = TravelExpense::find($id);

        if (!$travelExpense) {
            return redirect()->back()->with('error', __('Travel Expense / Voucher not found.'));
        }

        if (Auth::user()->type == 'employee') {
            $emp = Employee::where('user_id', Auth::user()->id)->first();
            if (!$emp || $travelExpense->employee_id != $emp->id) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'file|max:20480',
        ]);

        if ($request->hasFile('documents')) {
            $dir = 'travel_expenses/';
            foreach ($request->file('documents') as $file) {
                $fileName = time() . '_' . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $uploadRequest = new Request();
                $uploadRequest->files->set('file', $file);

                $path = Utility::upload_file($uploadRequest, 'file', $fileName, $dir, []);
                if ($path['flag'] == 1) {
                    $category = $travelExpense->type == 'travel' ? 'bill' : 'document';
                    TravelExpenseDocument::create([
                        'travel_expense_id' => $travelExpense->id,
                        'category' => $category,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path['url'],
                    ]);
                }
            }

            $travelExpense->document_requested = 0;
            $travelExpense->save();

            return redirect()->route('travel-expenses.index')->with('success', __('Documents uploaded successfully.'));
        }

        return redirect()->back()->with('error', __('Please select files to upload.'));
    }

    public function cancelDocumentRequest($id)
    {
        if (Auth::user()->type == 'company' || Auth::user()->type == 'hr' || Auth::user()->type == 'HR' || Auth::user()->type == 'super admin') {
            $travelExpense = TravelExpense::find($id);
            if ($travelExpense) {
                $travelExpense->document_requested = 0;
                $travelExpense->save();

                return redirect()->back()->with('success', __('Document request canceled successfully.'));
            }
            return redirect()->back()->with('error', __('Travel Expense / Voucher not found.'));
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}
