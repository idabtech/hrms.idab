<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeExport1;
use App\Exports\EmployeeExport2;
use App\Exports\EmployeeExport3;
use App\Imports\EmployeesImport;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Document;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Plan;
use App\Models\SubDepartment;
use App\Models\User;
use App\Models\Utility;
use App\Models\EmploymentType;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\JoiningLetter;
use App\Exports\EmployeesExport4;
use App\Models\Contract;
use App\Models\ExperienceCertificate;
use App\Models\LoginDetail;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\NOC;
use App\Models\PaySlip;
use App\Models\Shift;
use App\Models\Termination;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Srmklive\PayPal\Services\Str;

class EmployeeController extends Controller
{

    public function index()
    {

        if (Auth::user()->can('Manage Employee')) {
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('user_id', '=', Auth::user()->id)
                    ->where('is_active', 1)
                    ->whereHas('user', function ($q) {
                        $q->where('is_active', 1);
                    })
                    ->get();
            } else {
                $employees = Employee::where('created_by', Auth::user()->creatorId())
                    ->where('is_active', 1)
                    ->where('is_admin_staff', 0)
                    ->whereHas('user', function ($q) {
                        $q->where('is_active', 1);
                    })
                    ->with(['branch', 'department', 'designation', 'user'])
                    ->get();
            }

            return view('employee.index', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->can('Create Employee')) {
            $company_settings = Utility::settings();
            $company_shifts = isset($company_settings['company_shifts']) ? json_decode($company_settings['company_shifts'], true) : [];
            $documents = Document::where('created_by', Auth::user()->creatorId())->get();
            $branches = Branch::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments = Department::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $subdepartments = SubDepartment::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations = Designation::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $shift = Shift::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employees = User::where('created_by', Auth::user()->creatorId())->get();
            $employeesId = Auth::user()->employeeIdFormat($this->employeeNumber());

            $employmentTypes = EmploymentType::where('created_by', Auth::user()->creatorId())->get()->pluck('name','id');

            $leaveTypes = LeaveType::where('created_by', Auth::user()->creatorId())->get();

            return view('employee.create', compact('employees', 'employeesId', 'company_shifts', 'departments', 'subdepartments', 'designations', 'shift', 'documents', 'branches', 'company_settings', 'employmentTypes', 'leaveTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('Create Employee')) {

            $validator = Validator::make(
                $request->all(),
                [
                    'fname' => 'required',
                    'lname' => 'required',
                    'dob' => 'required',
                    'gender' => 'required',
                    'phone' => 'required|regex:/^[0-9+\s\-()]+$/',
                    'address' => 'required',
                    'email' => 'required|unique:users',
                    'password' => 'required',
                    'branch_id' => 'required',
                    'department_id' => 'required',
                    'subdepartment_id' => 'required',
                    'designation_id' => 'required',
                    'company_shift_time' => 'required',
                    'shift_id' => 'required',
                    'refresh_type' => 'required|in:fixed,flexible',
                    // 'lunch_start' => 'required_if:refresh_type,fixed',
                    // 'lunch_end' => 'required_if:refresh_type,fixed',
                    // 'tea_start' => 'required_if:refresh_type,fixed',
                    // 'tea_end' => 'required_if:refresh_type,fixed',
                    // 'lunch_minutes' => 'required_if:refresh_type,flexible|integer|min:0',
                    // 'tea_minutes' => 'required_if:refresh_type,flexible|integer|min:0',
                    'employment_type_id' => 'nullable|exists:employment_types,id',
                    'document.*' => 'required',
                ],
                [
                    'company_shift_time.required' => __('The Employee Time Zone field is required.'),
                    'refresh_type.required' => __('The Refresh Break Time field is required.'),
                    // 'refresh_type.in' => __('The selected Refresh Break Time is invalid.'),
                    // 'lunch_start.required_if' => __('Lunch start time is required when Refresh Break Time is fixed.'),
                    // 'lunch_end.required_if' => __('Lunch end time is required when Refresh Break Time is fixed.'),
                    // 'tea_start.required_if' => __('Tea start time is required when Refresh Break Time is fixed.'),
                    // 'tea_end.required_if' => __('Tea end time is required when Refresh Break Time is fixed.'),
                    // 'lunch_minutes.required_if' => __('Lunch minutes are required when Refresh Break Time is flexible.'),
                    // 'tea_minutes.required_if' => __('Tea minutes are required when Refresh Break Time is flexible.'),
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->withInput()->with('error', $messages->first());
            }

            $objUser = User::find(Auth::user()->creatorId());
            $total_employee = $objUser->countEmployees();
            $plan = Plan::find($objUser->plan);
            $date = date("Y-m-d H:i:s");
            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->where('created_by', Auth::user()->creatorId())->first();
            $passcode = random_int(100000, 999999);
            // new company default language
            if ($default_language == null) {
                $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
            }

            if ($total_employee < $plan->max_employees || $plan->max_employees == -1) {

                $user = User::create(
                    [
                        'name' => trim($request->input('fname') . ' ' . $request->input('lname')),
                        'email' => $request['email'],
                        'password' => Hash::make($request['password']),
                        'type' => 'employee',
                        'lang' => !empty($default_language) ? $default_language->value : 'en',
                        'created_by' => Auth::user()->creatorId(),
                        'email_verified_at' => $date,
                        'passcode' => $passcode
                    ]
                );
                $user->save();
                $compEmpRole = \Spatie\Permission\Models\Role::where('created_by', Auth::user()->creatorId())
                    ->where(function($q) { $q->where('name', 'employee')->orWhere('name', 'Employee'); })->first();
                if ($compEmpRole) {
                    $user->syncRoles([$compEmpRole->id]);
                } else {
                    $user->assignRole('Employee');
                }
            } else {
                return redirect()->back()->with('error', __('Your employee limit is over, Please upgrade plan.'));
            }


            if (!empty($request->document) && !is_null($request->document)) {
                $document_implode = implode(',', array_keys($request->document));
            } else {
                $document_implode = null;
            }

            $settings = Utility::settings();
            $company_shifts = isset($settings['company_shifts']) ? json_decode($settings['company_shifts'], true) : [];
            $company_start_time = null;
            $company_end_time   = null;

            if ($request->filled('company_shift_time') && isset($company_shifts[$request->company_shift_time])) {
                $selectedShift = $company_shifts[$request->company_shift_time];
                $company_start_time = $selectedShift['start'] ?? null;
                $company_end_time   = $selectedShift['end'] ?? null;
            } elseif (!empty($request->company_shift_time)) {
                $times = explode('-', $request->company_shift_time);
                $company_start_time = isset($times[0]) ? trim($times[0]) : null;
                $company_end_time   = isset($times[1]) ? trim($times[1]) : null;
            }

            // Handle lunch and tea break times based on shift_break_type

            // $record['company_start_time'] = $company_start_time;
            // $record['company_end_time']   = $company_end_time;
            $shift_break_type = $request->input('refresh_type');
            $record['refresh_type'] = $shift_break_type;
            if ($shift_break_type === 'fixed') {
                // Store fixed times
                $record['lunch_start'] = $request->input('lunch_start');
                $record['lunch_end'] = $request->input('lunch_end');
                $record['lunch_minutes'] = null;

                $record['tea_start'] = $request->input('tea_start');
                $record['tea_end'] = $request->input('tea_end');
                $record['tea_minutes'] = null;
            } else if ($shift_break_type === 'flexible') {
                // Store flexible minutes
                $record['lunch_start'] = null;
                $record['lunch_end'] = null;
                $record['lunch_minutes'] = $request->input('lunch_minutes', 0);

                $record['tea_start'] = null;
                $record['tea_end'] = null;
                $record['tea_minutes'] = $request->input('tea_minutes', 0);
            }

            $rawPhone = trim($request['phone'] ?? '');
            if (!empty($rawPhone)) {
                $codes = ['+971', '+880', '+966', '+977', '+91', '+44', '+61', '+49', '+33', '+81', '+65', '+92', '+94', '+1', '+7'];
                while (str_starts_with($rawPhone, '+')) {
                    $stripped = false;
                    foreach ($codes as $code) {
                        if (str_starts_with($rawPhone, $code)) {
                            $rawPhone = trim(substr($rawPhone, strlen($code)));
                            $stripped = true;
                            break;
                        }
                    }
                    if (!$stripped) {
                        $rawPhone = trim(ltrim($rawPhone, '+'));
                        break;
                    }
                }
            }
            $countryCode = trim($request->country_code ?? Utility::getDefaultCountryCode());
            $phonePayload = !empty($rawPhone) ? ($countryCode . ' ' . $rawPhone) : null;

            $employee = Employee::create(
                [
                    'user_id' => $user->id,
                    'name' => $request['fname'],
                    'last_name' => $request['lname'],
                    'dob' => $request['dob'],
                    'gender' => $request['gender'],
                    'phone' => $phonePayload,
                    'address' => $request['address'],
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']),
                    'employee_id' => $this->employeeNumber(),
                    'branch_id' => $request['branch_id'],
                    'department_id' => $request['department_id'],
                    'subdepartment_id' => $request['subdepartment_id'],
                    'designation_id' => $request['designation_id'],
                    'shift_id' => $request['shift_id'],
                    'company_doj' => $request['company_doj'],
                    'company_start_time' => $company_start_time,
                    'company_end_time'   => $company_end_time,
                    'employment_type_id' => $request['employment_type_id'],
                    'documents' => $document_implode,
                    'account_holder_name' => $request['account_holder_name'],
                    'account_number' => $request['account_number'],
                    'bank_name' => $request['bank_name'],
                    'bank_identifier_code' => $request['bank_identifier_code'],
                    'branch_location' => $request['branch_location'],
                    'tax_payer_id' => $request['tax_payer_id'],
                    // 'lunch_break' => $totalMinutes,
                    'refresh_type' => $request['refresh_type'],

                    // 'refresh_start' => $refreshStart,
                    // 'refresh_end' => $refreshEnd,
                    // 'refresh_minutes' => $refreshMinutes,
                    'created_by' => Auth::user()->creatorId(),
                ]
            );

            $employee->fill($record)->save();

            if ($request->hasFile('document')) {
                foreach ($request->document as $key => $document) {

                    $image_size = $request->file('document')[$key]->getSize();
                    $result = Utility::updateStorageLimit(Auth::user()->creatorId(), $image_size);

                    if ($result == 1) {
                        $filenameWithExt = $request->file('document')[$key]->getClientOriginalName();
                        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension = $request->file('document')[$key]->getClientOriginalExtension();
                        $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                        $dir = 'uploads/document/';

                        $path = Utility::upload_coustom_file($request, 'document', $fileNameToStore, $dir, $key, []);

                        if ($path['flag'] == 1) {
                            $url = $path['url'];
                        } else {
                            return redirect()->back()->with('error', __($path['msg']));
                        }

                        // Check if this document_id also has a text value (both type)
                        $textVal = $request->input("document_text.{$key}");
                        if (!is_null($textVal) && $textVal !== '') {
                            $storeValue = json_encode(['text' => $textVal, 'file' => $path['url']]);
                        } else {
                            $storeValue = $path['url'];
                        }

                        EmployeeDocument::create([
                            'employee_id'    => $employee['employee_id'],
                            'document_id'    => $key,
                            'document_value' => $storeValue,
                            'created_by'     => Auth::user()->creatorId(),
                        ]);
                    }
                }
            }

            // Handle text-only document values (type = 'text', no file uploaded)
            if ($request->has('document_text')) {
                foreach ($request->document_text as $key => $textValue) {
                    // Skip if a file was also uploaded for this key (already handled above)
                    if ($request->hasFile('document') && isset($request->file('document')[$key])) {
                        continue;
                    }
                    if (!empty($textValue)) {
                        EmployeeDocument::updateOrCreate(
                            [
                                'employee_id' => $employee['employee_id'],
                                'document_id' => $key,
                            ],
                            [
                                'document_value' => $textValue,
                                'created_by'     => Auth::user()->creatorId(),
                            ]
                        );
                    }
                }
            }
            $setings = Utility::settings();

            // ── Sync assigned leave types ──────────────────────────────────────
            if ($request->has('leave_types') && is_array($request->leave_types)) {
                $syncData = [];
                foreach ($request->leave_types as $leaveTypeId => $data) {
                    if (!empty($data['checked'])) {
                        $syncData[$leaveTypeId] = [
                            'total_days' => $data['days'] ?? 0,
                            'is_paid'    => isset($data['is_paid']) ? 1 : 0,
                        ];
                    }
                }
                $employee->leaveTypes()->sync($syncData);
            }

            if ($setings['new_employee'] == 1) {
                $department = Department::find($request['department_id']);
                $branch = Branch::find($request['branch_id']);
                $designation = Designation::find($request['designation_id']);
                $uArr = [
                    'employee_email' => $user->email,
                    'employee_password' => $request->password,
                    'employee_name' => trim($request->input('fname') . ' ' . $request->input('lname')),
                    'employee_branch' => !empty($branch->name) ? $branch->name : '',
                    'employee_department' => !empty($department->name) ? $department->name : '',
                    'employee_designation' => !empty($designation->name) ? $designation->name : '',
                    'employee_passcode' => $user->passcode,
                ];
                $resp = Utility::sendEmailTemplate('new_employee', [$user->id => $user->email], $uArr);

                return redirect()->route('employee.index')->with('success', __('Employee successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : '') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
            }
            return redirect()->back()->with('success', __('Employee successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        try {
            $empId = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            if (is_numeric($id)) {
                $empId = $id;
            } else {
                return redirect()->back()->with('error', __('Employee Not Found.'));
            }
        }
        if (Auth::user()->can('Edit Employee') || (Auth::user()->type == 'employee' && Auth::user()->employee && Auth::user()->employee->id == $empId)) {
            $company_settings = Utility::settings();
            $company_shifts = isset($company_settings['company_shifts']) ? json_decode($company_settings['company_shifts'], true) : [];
            $employee = Employee::find($empId);
            if (!$employee) {
                return redirect()->back()->with('error', __('Employee Not Found.'));
            }
            $documents = Document::where('created_by', Auth::user()->creatorId())->get();
            $employeeDocuments = EmployeeDocument::where('employee_id', $employee->id)->get()->sortByDesc('document_value')->keyBy('document_id');
            $branches = Branch::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments = Department::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $selectedDeptId = old('department_id', $employee->department_id);
            if (!empty($selectedDeptId)) {
                $subdepartments = SubDepartment::where(['department' => $selectedDeptId, 'created_by' => Auth::user()->creatorId()])->get()->pluck('name', 'id');
            } else {
                $subdepartments = SubDepartment::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            }
            $designations = Designation::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $shift = Shift::where('created_by', Auth::user()->creatorId())->whereNull('date')->get()->pluck('name', 'id');
            $employeesId = Auth::user()->employeeIdFormat($employee->employee_id);

            $employmentTypes = EmploymentType::where('created_by', Auth::user()->creatorId())->get()->pluck('name','id');

            $leaveTypes = LeaveType::where('created_by', Auth::user()->creatorId())->get();
            $assignedLeaveTypes = $employee->leaveTypes()->get()->keyBy('id')->map(function ($lt) {
                return [
                    'total_days' => $lt->pivot->total_days,
                    'is_paid'    => $lt->pivot->is_paid,
                ];
            })->toArray();

            return view('employee.edit', compact('company_settings', 'company_shifts', 'employee', 'employeesId', 'branches', 'departments', 'subdepartments', 'shift', 'designations', 'documents', 'employeeDocuments', 'employmentTypes', 'leaveTypes', 'assignedLeaveTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function requestDocument(Request $request)
    {
        if (Auth::user()->can('Edit Employee') || Auth::user()->type == 'hr' || Auth::user()->type == 'company') {
            $employee_id = $request->input('employee_id');
            $document_id = $request->input('document_id');
            $is_requested = $request->input('is_requested', 1);

            $empDoc = EmployeeDocument::where('employee_id', $employee_id)
                ->where('document_id', $document_id)
                ->first();

            if ($empDoc) {
                $empDoc->is_requested = $is_requested;
                $empDoc->save();
            } else {
                EmployeeDocument::create([
                    'employee_id'    => $employee_id,
                    'document_id'    => $document_id,
                    'document_value' => '',
                    'is_requested'   => $is_requested,
                    'created_by'     => Auth::user()->creatorId(),
                ]);
            }

            return response()->json([
                'success' => true,
                'is_requested' => (int)$is_requested,
                'message' => $is_requested ? __('Document requested successfully.') : __('Document request cancelled.')
            ]);
        }

        return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
    }

    public function update(Request $request, $id)
    {
        try {
            $empId = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            if (is_numeric($id)) {
                $empId = $id;
            } else {
                return redirect()->back()->with('error', __('Employee Not Found.'));
            }
        }

        $isEmployeeUser = (Auth::user()->type == 'employee');
        if (Auth::user()->can('Edit Employee') || ($isEmployeeUser && Auth::user()->employee && Auth::user()->employee->id == $empId)) {
            $employee = Employee::findOrFail($empId);

            // Handle Employee-only document upload submission
            if ($isEmployeeUser) {
                $this->processDocumentUploads($request, $employee->id);
                return redirect()->back()->with('success', __('Document updated successfully.'));
            }

            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'last_name' => 'required',
                    'dob' => 'required',
                    'email' => 'required|unique:employees,email,' . $empId . ',id',
                    'gender' => 'required',
                    'phone' => 'required|regex:/^[0-9+\s\-()]+$/',
                    'address' => 'required',
                    'employment_type_id' => 'nullable|exists:employment_types,id',
                    'company_shift_time' => 'required',
                    'refresh_type' => 'required|in:fixed,flexible',
                    'lunch_start' => 'required_if:refresh_type,fixed',
                    'lunch_end' => 'required_if:refresh_type,fixed',
                    'tea_start' => 'required_if:refresh_type,fixed',
                    'tea_end' => 'required_if:refresh_type,fixed',
                    'lunch_minutes' => 'required_if:refresh_type,flexible|integer|min:0',
                    'tea_minutes' => 'required_if:refresh_type,flexible|integer|min:0',
                ],
                [
                    'company_shift_time.required' => __('The Employee Time Zone field is required.'),
                    'refresh_type.required' => __('The Refresh Break Time field is required.'),
                    'refresh_type.in' => __('The selected Refresh Break Time is invalid.'),
                    'lunch_start.required_if' => __('Lunch start time is required when Refresh Break Time is fixed.'),
                    'lunch_end.required_if' => __('Lunch end time is required when Refresh Break Time is fixed.'),
                    'tea_start.required_if' => __('Tea start time is required when Refresh Break Time is fixed.'),
                    'tea_end.required_if' => __('Tea end time is required when Refresh Break Time is fixed.'),
                    'lunch_minutes.required_if' => __('Lunch minutes are required when Refresh Break Time is flexible.'),
                    'tea_minutes.required_if' => __('Tea minutes are required when Refresh Break Time is flexible.'),
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->withInput()->withErrors($validator)->with('error', $messages->first());
            }

            $employee = Employee::findOrFail($empId);
            $input = $request->all();

            $company_start_time = null;
            $company_end_time   = null;

            $refreshSettings = Utility::settings();
            $settings = Utility::settings();
            $company_shifts = isset($settings['company_shifts']) ? json_decode($settings['company_shifts'], true) : [];
            if ($request->filled('company_shift_time') && isset($company_shifts[$request->company_shift_time])) {
                $selectedShift = $company_shifts[$request->company_shift_time];
                $company_start_time = $selectedShift['start'] ?? null;
                $company_end_time   = $selectedShift['end'] ?? null;
            } elseif (!empty($request->company_shift_time)) {
                $times = explode('-', $request->company_shift_time);
                $company_start_time = isset($times[0]) ? trim($times[0]) : null;
                $company_end_time   = isset($times[1]) ? trim($times[1]) : null;
            }

            $employee->company_start_time = $company_start_time;
            $employee->company_end_time   = $company_end_time;

            // Handle lunch and tea break times based on shift_break_type
            $shift_break_type = $request->input('refresh_type');
            $input['refresh_type'] = $shift_break_type;
            $input['employment_type_id'] = $request->input('employment_type_id');

            if ($shift_break_type === 'fixed') {
                // Store fixed times
                $input['lunch_start'] = $request->input('lunch_start');
                $input['lunch_end'] = $request->input('lunch_end');
                $input['lunch_minutes'] = null;

                $input['tea_start'] = $request->input('tea_start');
                $input['tea_end'] = $request->input('tea_end');
                $input['tea_minutes'] = null;
            } else if ($shift_break_type === 'flexible') {
                // Store flexible minutes
                $input['lunch_start'] = null;
                $input['lunch_end'] = null;
                $input['lunch_minutes'] = $request->input('lunch_minutes', 0);

                $input['tea_start'] = null;
                $input['tea_end'] = null;
                $input['tea_minutes'] = $request->input('tea_minutes', 0);
            }

            // Process multi-file and text document uploads
            $this->processDocumentUploads($request, $employee->id);

            if (!empty($request->document) && !is_null($request->document)) {
                $document_implode = implode(',', array_keys($request->document));
            } else {
                $document_implode = null;
            }

            if (!empty($request->phone)) {
                $rawPhone = trim($request->phone);
                $codes = ['+971', '+880', '+966', '+977', '+91', '+44', '+61', '+49', '+33', '+81', '+65', '+92', '+94', '+1', '+7'];
                while (str_starts_with($rawPhone, '+')) {
                    $stripped = false;
                    foreach ($codes as $code) {
                        if (str_starts_with($rawPhone, $code)) {
                            $rawPhone = trim(substr($rawPhone, strlen($code)));
                            $stripped = true;
                            break;
                        }
                    }
                    if (!$stripped) {
                        $rawPhone = trim(ltrim($rawPhone, '+'));
                        break;
                    }
                }
                $countryCode = trim($request->country_code ?? Utility::getDefaultCountryCode());
                $input['phone'] = !empty($rawPhone) ? ($countryCode . ' ' . $rawPhone) : null;
            }

            $input['documents'] = $document_implode;

            $employee->fill($input)->save();

            // ── Sync assigned leave types ──────────────────────────────────────
            if ($request->has('leave_types')) {
                $syncData = [];
                foreach (($request->leave_types ?? []) as $leaveTypeId => $data) {
                    if (!empty($data['checked'])) {
                        $syncData[$leaveTypeId] = [
                            'total_days' => $data['days'] ?? 0,
                            'is_paid'    => isset($data['is_paid']) ? 1 : 0,
                        ];
                    }
                }
                $employee->leaveTypes()->sync($syncData);
            }

            if (!empty($employee->user_id)) {
                $user = User::find($employee->user_id);
                if ($user) {
                    if (!empty($request->email)) {
                        $user->email = $request->email;
                    }
                    if ($request->has('passcode')) {
                        $user->passcode = $request->passcode;
                    }
                    $user->save();
                }
            }

            if ($request->salary) {
                return redirect()->route('setsalary.index')->with('success', 'Employee successfully updated.');
            }

            if (Auth::user()->type != 'employee') {
                return redirect()->back()->with('success', __('Employee successfully updated.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
            } else {
                return redirect()->route('employee.show', Crypt::encrypt($employee->id))->with('success', __('Employee successfully updated.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('Delete Employee')) {
            $employee = Employee::findOrFail($id);
            $user = User::where('id', '=', $employee->user_id)->first();
            $emp_documents = EmployeeDocument::where('employee_id', $employee->employee_id)->get();
            $ContractEmployee = Contract::where('employee_name', '=', $employee->user_id)->get();
            $payslips = PaySlip::where('employee_id', $id)->get();
            $employee->delete();
            $user->delete();

            foreach ($ContractEmployee as $contractdelete) {
                $contractdelete->delete();
            }

            foreach ($payslips as $payslip) {
                $payslip->delete();
            }

            $dir = storage_path('uploads/document/');
            foreach ($emp_documents as $emp_document) {

                $emp_document->delete();
                if (!empty($emp_document->document_value)) {
                    // Support both old plain-path records and new JSON records (both type)
                    $parsed    = $emp_document->getParsedValue();
                    $fileToDelete = $parsed['file'];
                    if ($fileToDelete) {
                        $file_path = 'uploads/document/' . $fileToDelete;
                        $result = Utility::changeStorageLimit(Auth::user()->creatorId(), $file_path);
                    }
                }
            }

            return redirect()->route('employee.index')->with('success', 'Employee successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id)
    {
        if (Auth::user()->can('Show Employee')) {
            try {
                $empId = Crypt::decrypt($id);
            } catch (\Throwable $e) {
                if (is_numeric($id)) {
                    $empId = $id;
                } else {
                    return redirect()->back()->with('error', __('Employee not available'));
                }
            }
            $employee = Employee::find($empId);
            if (!$employee) {
                return redirect()->back()->with('error', __('Employee not available'));
            }
            $documents = Document::where('created_by', Auth::user()->creatorId())->get();
            $branches = Branch::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments = Department::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations = Designation::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employeesId = Auth::user()->employeeIdFormat($employee->employee_id);

            return view('employee.show', compact('employee', 'employeesId', 'branches', 'departments', 'designations', 'documents'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function employeeNumber()
    {
        $latest = Employee::where('created_by', '=', Auth::user()->creatorId())->latest('id')->first();
        if (!$latest) {
            return 1;
        }

        return $latest->employee_id + 1;
    }

    public function importFile()
    {
        return view('employee.import');
    }

    public function exportFile()
    {
        return view('employee.export-excels');
    }

    protected function ensureRequiredTablesExist(array $tables)
    {
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                throw new \Exception("Missing required table: `$table`. Please run migrations.");
            }
        }
    }

    public function import(Request $request)
    {
        try {
            $this->ensureRequiredTablesExist([
                'branches',
                'departments',
                'sub_departments',
                'designations',
                'shifts',
                'employees',
                'users'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $rows = (new EmployeesImport())->toArray($request->file('file'))[0];

        if (count($rows) <= 1) {
            return redirect()->back()->with('error', 'The file is empty or improperly formatted.');
        }

        $totalRecords = count($rows) - 1;
        $importErrors = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            DB::beginTransaction();
            try {
                // === Basic Validation ===
                if (empty($row[6]) || empty($row[7])) {
                    throw new \Exception("Missing required email or password at row $i.");
                }

                $existingEmployee = Employee::where('email', $row[6])->first();
                $existingUser = User::where('email', $row[6])->first();

                $branchId = $this->check_branch($row[9]);
                $departmentId = $this->check_department($row[10], $branchId);
                $subDeptId = $this->check_sub_department($row[11], $departmentId);
                $designationId = $this->check_designation($row[12], $departmentId);
                $shiftId = $this->check_shift($row[13]);

                if ($existingEmployee && $existingUser) {
                    $employee = $existingEmployee;
                } else {
                    $user = new User();
                    $user->name = $row[0] . ' ' . $row[1];
                    $user->email = $row[6];
                    $user->password = Hash::make($row[7]);
                    $user->type = 'employee';
                    $user->lang = 'en';
                    $user->created_by = Auth::user()->creatorId();
                    $user->save();
                    $compEmpRole = \Spatie\Permission\Models\Role::where('created_by', Auth::user()->creatorId())
                        ->where(function($q) { $q->where('name', 'employee')->orWhere('name', 'Employee'); })->first();
                    if ($compEmpRole) {
                        $user->syncRoles([$compEmpRole->id]);
                    } else {
                        $user->assignRole('Employee');
                    }

                    $employee = new Employee();
                    $employee->employee_id = $this->employeeNumber();
                    $employee->user_id = $user->id;
                }

                $employee->fill([
                    'name' => $row[0],
                    'last_name' => $row[1],
                    'dob' => $row[2],
                    'gender' => $row[3],
                    'phone' => $row[4],
                    'address' => $row[5],
                    'email' => $row[6],
                    'password' => Hash::make($row[7]),
                    'branch_id' => $branchId,
                    'department_id' => $departmentId,
                    'subdepartment_id' => $subDeptId,
                    'designation_id' => $designationId,
                    'shift_id' => $shiftId,
                    'company_doj' => $row[14],
                    'account_holder_name' => $row[15],
                    'account_number' => $row[16],
                    'bank_name' => $row[17],
                    'bank_identifier_code' => $row[18],
                    'branch_location' => $row[19],
                    'tax_payer_id' => $row[20],
                    'created_by' => Auth::user()->creatorId(),
                ]);

                $employee->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                $friendlyMessage = "An error occurred while processing this row. Please review the data and try again.";

                $errorText = $e->getMessage();

                if (str::contains($errorText, ['Unknown column'])) {
                    $friendlyMessage = "One of the required columns in the database is missing. Please contact the administrator.";
                } elseif (Str::contains($errorText, ['Integrity constraint violation'])) {
                    $friendlyMessage = "This entry violates a database rule (e.g., duplicate email or invalid foreign key).";
                } elseif (Str::contains($errorText, ['Cannot add or update a child row'])) {
                    $friendlyMessage = "Invalid reference data (e.g., branch, department, sub department, shift or designation might not exist).";
                } elseif (Str::contains($errorText, ['SQLSTATE'])) {
                    $friendlyMessage = "A database error occurred. Please verify the data in this row.";
                } elseif (Str::contains($errorText, ['Call to undefined method'])) {
                    $friendlyMessage = "A technical error occurred. Please inform the developer team.";
                } elseif (Str::contains($errorText, ['Trying to access array offset'])) {
                    $friendlyMessage = "Some expected data is missing in this row. Please review the file format.";
                }

                $importErrors[] = [
                    'row_number' => $i + 1,
                    'row_data' => implode(', ', $row),
                    'error_message' => $friendlyMessage
                ];
            }
        }

        if (empty($importErrors)) {
            return redirect()->back()->with('success', "All {$totalRecords} records imported successfully.");
        } else {
            $errorCount = count($importErrors);
            $successCount = $totalRecords - $errorCount;

            Session::put('import_errors', $importErrors);

            $errorSummary = "{$successCount} records imported, {$errorCount} failed. ";
            $errorSummary .= '<br><a href="javascript:void(0)" '
                . 'onclick="var el=document.getElementById(\'importErrorDetails\'); '
                . 'el.style.display = (el.style.display===\'none\'?\'block\':\'none\'); '
                . 'return false;" '
                . 'style="color:#007bff; text-decoration:underline;">'
                . 'See details below</a>';

            $htmlDetails = '<div id="importErrorDetails" style="display:none; '
                . 'margin-top:10px; padding:10px; background: rgb(255 49 49); '
                . 'border:1px solid rgb(255, 95, 95); border-radius:4px; max-height:200px; overflow-y:auto;">'
                . '<ul id="errorList" style="padding-left:10px; margin:0;">';

            foreach ($importErrors as $index => $err) {
                $htmlDetails .= '<li id="error-' . $index . '" style="margin-bottom:10px; position:relative; padding:10px; border:1px solid #ddd; border-radius:4px; list-style-type: none;">'
                    . '<button onclick="removeError(\'error-' . $index . '\')" style="position:absolute; top:-10px; right:-10px; background:#ffd400; color:white; border:none; border-radius:50%; width:24px; height:24px; font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="Remove">&times;</button>'
                    . '<strong>Row ' . htmlspecialchars($err['row_number']) . ':</strong> '
                    . htmlspecialchars($err['error_message']) . '<br>'
                    . '<em>Data:</em> ' . htmlspecialchars($err['row_data'])
                    . '</li>';
            }

            $htmlDetails .= '</ul></div>';

            $escapedErrorSummary = addslashes($errorSummary . $htmlDetails);

            return redirect()->back()->with('error', $escapedErrorSummary);
        }
    }
    public function check_branch($branch_name)
    {
        $branch = Branch::where(['name' => $branch_name, 'created_by' => Auth::user()->creatorId()])->first();
        if (isset($branch->id)) {
            $branch_id = $branch->id;
        } else {
            $branch = new Branch();
            $branch->name = $branch_name;
            $branch->created_by = Auth::user()->creatorId();
            $branch->save();
            $branch_id = $branch->id;
        }
        return $branch_id;
    }
    public function check_department($department_name, $branch_id)
    {
        $department = Department::where(['branch_id' => $branch_id, 'name' => $department_name, 'created_by' => Auth::user()->creatorId()])->first();
        if (isset($department->id)) {
            $department_id = $department->id;
        } else {
            $department = new Department();
            $department->branch_id = $branch_id;
            $department->name = $department_name;
            $department->slug = strtoupper(substr($department_name, 0, 3));
            $department->created_by = Auth::user()->creatorId();
            $department->save();
            $department_id = $department->id;
        }
        return $department_id;
    }

    public function check_sub_department($sub_department_name, $department_id)
    {
        $subdepartment = SubDepartment::where(['department' => $department_id, 'name' => $sub_department_name, 'created_by' => Auth::user()->creatorId()])->first();
        if (isset($subdepartment->id)) {
            $sub_department_id = $subdepartment->id;
        } else {
            $sub_department = new SubDepartment();
            $sub_department->department = $department_id;
            $sub_department->name = $sub_department_name;
            $sub_department->created_by = Auth::user()->creatorId();
            $sub_department->save();
            $sub_department_id = $sub_department->id;
        }
        return $sub_department_id;
    }

    public function check_designation($designation_name, $department_id)
    {
        $designation = Designation::where(['department_id' => $department_id, 'name' => $designation_name, 'created_by' => Auth::user()->creatorId()])->first();
        if (!empty($designation)) {
            $designation_id = $designation->id;
        } else {
            $designation = new Designation();
            $designation->department_id = $department_id;
            $designation->name = $designation_name;
            $designation->created_by = Auth::user()->creatorId();
            $designation->save();
            $designation_id = $designation->id;
        }
        return $designation_id;
    }

    public function check_shift($shift_name)
    {
        $shift = Shift::where(['name' => $shift_name, 'created_by' => Auth::user()->creatorId()])->first();
        if (!empty($shift)) {
            $shift_id = $shift->id;
        } else {
            $shift = new Shift();
            $shift->name = $shift_name;
            $shift->created_by = Auth::user()->creatorId();
            $shift->save();
            $shift_id = $shift->id;
        }
        return $shift_id;
    }

    public function exportExcelD()
    {
        $name = 'employee_' . date('Y-m-d i:h:s');
        $data = Excel::download(new EmployeesExport4(), $name . '.xlsx');

        return $data;
    }
    public function exportExcelA()
    {
        $name = 'employee-form-A_' . date('Y-m-d i:h:s');
        $data = Excel::download(new EmployeeExport1(), $name . '.xlsx');

        return $data;
    }
    public function exportExcelB()
    {
        $name = 'employee-form-B_' . date('Y-m-d i:h:s');
        $data = Excel::download(new EmployeeExport2(), $name . '.xlsx');

        return $data;
    }
    public function exportExcelC()
    {
        $name = 'employee-form-C_' . date('Y-m-d i:h:s');
        $data = Excel::download(new EmployeeExport3(), $name . '.xlsx');

        return $data;
    }

    public function profile(Request $request)
    {
        if (Auth::user()->can('Manage Employee Profile')) {
            $employees = Employee::where('created_by', Auth::user()->creatorId())->with(['designation', 'user']);
            if (!empty($request->branch_id)) {
                $employees->where('branch_id', $request->branch_id);
            }
            if (!empty($request->department_id)) {
                $employees->where('department_id', $request->department_id);
            }
            if (!empty($request->designation_id)) {
                $employees->where('designation_id', $request->designation_id);
            }
            $employees = $employees->get();

            $brances = Branch::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');

            $departments = Department::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');

            $designations = Designation::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('employee.profile', compact('employees', 'departments', 'designations', 'brances'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function profileShow($id)
    {
        if (Auth::user()->can('Show Employee Profile')) {
            try {
                $empId = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                if (is_numeric($id)) {
                    $empId = $id;
                } else {
                    return redirect()->back()->with('error', __('Employee Not Found.'));
                }
            }
            $documents = Document::where('created_by', Auth::user()->creatorId())->get();
            $branches = Branch::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments = Department::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations = Designation::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employee = Employee::find($empId);
            if ($employee == null) {
                $employee = Employee::where('user_id', $empId)->first();
            }
            if (!$employee) {
                return redirect()->back()->with('error', __('Employee Not Found.'));
            }

            $employeesId = Auth::user()->employeeIdFormat($employee->employee_id);

            return view('employee.show', compact('employee', 'employeesId', 'branches', 'departments', 'designations', 'documents'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function lastLogin(Request $request)
    {
        $users = User::where('created_by', Auth::user()->creatorId())->where('id', '!=', Auth::user()->id)->get();

        $time = date_create($request->month);
        $firstDayofMOnth = (date_format($time, 'Y-m-d'));
        $lastDayofMonth = \Carbon\Carbon::parse($request->month)->endOfMonth()->toDateString();
        $objUser = Auth::user();

        $usersList = User::where('created_by', '=', $objUser->creatorId())->where('id', '!=', Auth::id())
            ->whereNotIn('type', ['super admin', 'company'])->get()->pluck('name', 'id');
        $usersList->prepend('All', '');
        if ($request->month == null) {
            $userdetails = DB::table('login_details')
                ->join('users', 'login_details.user_id', '=', 'users.id')
                ->select(DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))
                ->where(['login_details.created_by' => Auth::user()->creatorId()])
                ->where('login_details.user_id', '!=', Auth::id())
                ->whereMonth('date', date('m'))->whereYear('date', date('Y'));
        } else {
            $userdetails = DB::table('login_details')
                ->join('users', 'login_details.user_id', '=', 'users.id')
                ->select(DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))
                ->where(['login_details.created_by' => Auth::user()->creatorId()])->where('login_details.user_id', '!=', Auth::id());
        }
        if (!empty($request->month)) {
            $userdetails->where('date', '>=', $firstDayofMOnth);
            $userdetails->where('date', '<=', $lastDayofMonth);
        }
        if (!empty($request->employee)) {
            $userdetails->where(['user_id' => $request->employee]);
        }
        $userdetails = $userdetails->get();

        return view('employee.lastLogin', compact('users', 'usersList', 'userdetails'));
    }

    public function employeeJson(Request $request)
    {
        $employees = Employee::where('branch_id', $request->branch)->get()->pluck('name', 'id')->toArray();

        return response()->json($employees);
    }

    public function joiningletterPdf($id)
    {
        $users = Auth::user();

        $currantLang = $users->currentLanguage();
        $joiningletter = JoiningLetter::where('lang', $currantLang)->where('created_by', Auth::user()->creatorId())->first();
        $date = date('Y-m-d');
        $employees = Employee::where('id', $id)->where('created_by', Auth::user()->creatorId())->first();
        $settings = Utility::settings();

        if ($employees && $employees->company_start_time && $employees->company_end_time) {
            $startTime = $employees->company_start_time;
            $endTime   = $employees->company_end_time;
        } else {
            $startTime  = Utility::getValByName('company_start_time');
            $endTime    = Utility::getValByName('company_end_time');
        }
        $secs = strtotime($startTime) - strtotime("00:00");
        $result = date("H:i", strtotime($endTime) - $secs);
        $obj = [
            'date' => Auth::user()->dateFormat($date),
            'app_name' => env('APP_NAME'),
            'employee_name' => $employees->name,
            'address' => !empty($employees->address) ? $employees->address : '',
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'start_date' => !empty($employees->company_doj) ? $employees->company_doj : '',
            'branch' => !empty($employees->Branch->name) ? $employees->Branch->name : '',
            'start_time' => !empty($startTime) ? $startTime : '',
            'end_time' => !empty($endTime) ? $endTime : '',
            'total_hours' => $result,
        ];

        $joiningletter->content = JoiningLetter::replaceVariable($joiningletter->content, $obj);
        return view('employee.template.joiningletterpdf', compact('joiningletter', 'employees'));
    }
    public function joiningletterDoc($id)
    {
        $users = Auth::user();

        $currantLang = $users->currentLanguage();
        $joiningletter = JoiningLetter::where('lang', $currantLang)->where('created_by', Auth::user()->creatorId())->first();
        $date = date('Y-m-d');
        $employees = Employee::where('id', $id)->where('created_by', Auth::user()->creatorId())->first();

        if ($employees && $employees->company_start_time && $employees->company_end_time) {
            $startTime = $employees->company_start_time;
            $endTime   = $employees->company_end_time;
        } else {
            $startTime  = Utility::getValByName('company_start_time');
            $endTime    = Utility::getValByName('company_end_time');
        }

        $settings = Utility::settings();
        $secs = strtotime($startTime) - strtotime("00:00");
        $result = date("H:i", strtotime($endTime) - $secs);

        $obj = [
            'date' => Auth::user()->dateFormat($date),

            'app_name' => env('APP_NAME'),
            'employee_name' => $employees->name,
            'address' => !empty($employees->address) ? $employees->address : '',
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'start_date' => !empty($employees->company_doj) ? $employees->company_doj : '',
            'branch' => !empty($employees->Branch->name) ? $employees->Branch->name : '',
            'start_time' => !empty($startTime) ? $startTime : '',
            'end_time' => !empty($endTime) ? $endTime : '',
            'total_hours' => $result,

        ];
        $joiningletter->content = JoiningLetter::replaceVariable($joiningletter->content, $obj);
        return view('employee.template.joiningletterdocx', compact('joiningletter', 'employees'));
    }

    public function ExpCertificatePdf($id)
    {
        $currantLang = Cookie::get('LANGUAGE');
        if (!isset($currantLang)) {
            $currantLang = 'en';
        }
        $termination = Termination::where('employee_id', $id)->where('created_by', Auth::user()->creatorId())->first();
        $experience_certificate = ExperienceCertificate::where('lang', $currantLang)->where('created_by', Auth::user()->creatorId())->first();
        $date = date('Y-m-d');
        $employees = Employee::where('id', $id)->where('created_by', Auth::user()->creatorId())->first();
        $settings = Utility::settings();

        if ($employees && $employees->company_start_time && $employees->company_end_time) {
            $startTime = $employees->company_start_time;
            $endTime   = $employees->company_end_time;
        } else {
            $startTime  = Utility::getValByName('company_start_time');
            $endTime    = Utility::getValByName('company_end_time');
        }

        $secs = strtotime($startTime) - strtotime("00:00");
        $result = date("H:i", strtotime($endTime) - $secs);
        $date1 = date_create($employees->company_doj);
        $date2 = date_create($employees->termination_date);
        $diff = date_diff($date1, $date2);
        $duration = $diff->format("%a days");

        if (!empty($termination->termination_date)) {

            $obj = [
                'date' => Auth::user()->dateFormat($date),
                'app_name' => env('APP_NAME'),
                'employee_name' => $employees->name,
                'payroll' => !empty($employees->salaryType->name) ? $employees->salaryType->name : '',
                'duration' => $duration,
                'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',

            ];
        } else {
            return redirect()->back()->with('error', __('Termination date is required.'));
        }


        $experience_certificate->content = ExperienceCertificate::replaceVariable($experience_certificate->content, $obj);
        return view('employee.template.ExpCertificatepdf', compact('experience_certificate', 'employees'));
    }
    public function ExpCertificateDoc($id)
    {
        $currantLang = Cookie::get('LANGUAGE');
        if (!isset($currantLang)) {
            $currantLang = 'en';
        }
        $termination = Termination::where('employee_id', $id)->where('created_by', Auth::user()->creatorId())->first();
        $experience_certificate = ExperienceCertificate::where('lang', $currantLang)->where('created_by', Auth::user()->creatorId())->first();
        $date = date('Y-m-d');
        $employees = Employee::where('id', $id)->where('created_by', Auth::user()->creatorId())->first();;
        $settings = Utility::settings();

        if ($employees && $employees->company_start_time && $employees->company_end_time) {
            $startTime = $employees->company_start_time;
            $endTime   = $employees->company_end_time;
        } else {
            $startTime  = Utility::getValByName('company_start_time');
            $endTime    = Utility::getValByName('company_end_time');
        }
        $secs = strtotime($startTime) - strtotime("00:00");
        $result = date("H:i", strtotime($endTime) - $secs);
        $date1 = date_create($employees->company_doj);
        $date2 = date_create($employees->termination_date);
        $diff = date_diff($date1, $date2);
        $duration = $diff->format("%a days");
        if (!empty($termination->termination_date)) {
            $obj = [
                'date' => Auth::user()->dateFormat($date),
                'app_name' => env('APP_NAME'),
                'employee_name' => $employees->name,
                'payroll' => !empty($employees->salaryType->name) ? $employees->salaryType->name : '',
                'duration' => $duration,
                'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',

            ];
        } else {
            return redirect()->back()->with('error', __('Termination date is required.'));
        }

        $experience_certificate->content = ExperienceCertificate::replaceVariable($experience_certificate->content, $obj);
        return view('employee.template.ExpCertificatedocx', compact('experience_certificate', 'employees'));
    }
    public function NocPdf($id)
    {
        $users = Auth::user();

        $currantLang = $users->currentLanguage();
        $noc_certificate = NOC::where('lang', $currantLang)->where('created_by', Auth::user()->creatorId())->first();
        $date = date('Y-m-d');
        $employees = Employee::where('id', $id)->where('created_by', Auth::user()->creatorId())->first();
        $settings = Utility::settings();
        if ($employees && $employees->company_start_time && $employees->company_end_time) {
            $startTime = $employees->company_start_time;
            $endTime   = $employees->company_end_time;
        } else {
            $startTime  = Utility::getValByName('company_start_time');
            $endTime    = Utility::getValByName('company_end_time');
        }
        $secs = strtotime($startTime) - strtotime("00:00");
        $result = date("H:i", strtotime($endTime) - $secs);


        $obj = [
            'date' => Auth::user()->dateFormat($date),
            'employee_name' => $employees->name,
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'app_name' => env('APP_NAME'),
        ];

        $noc_certificate->content = NOC::replaceVariable($noc_certificate->content, $obj);
        return view('employee.template.Nocpdf', compact('noc_certificate', 'employees'));
    }
    public function NocDoc($id)
    {
        $users = Auth::user();

        $currantLang = $users->currentLanguage();
        $noc_certificate = NOC::where('lang', $currantLang)->where('created_by', Auth::user()->creatorId())->first();
        $date = date('Y-m-d');
        $employees = Employee::where('id', $id)->where('created_by', Auth::user()->creatorId())->first();
        $settings = Utility::settings();

        if ($employees && $employees->company_start_time && $employees->company_end_time) {
            $startTime = $employees->company_start_time;
            $endTime   = $employees->company_end_time;
        } else {
            $startTime  = Utility::getValByName('company_start_time');
            $endTime    = Utility::getValByName('company_end_time');
        }

        $secs = strtotime($startTime) - strtotime("00:00");
        $result = date("H:i", strtotime($endTime) - $secs);


        $obj = [
            'date' => Auth::user()->dateFormat($date),
            'employee_name' => $employees->name,
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'app_name' => env('APP_NAME'),
        ];

        $noc_certificate->content = NOC::replaceVariable($noc_certificate->content, $obj);
        return view('employee.template.Nocdocx', compact('noc_certificate', 'employees'));
    }

    public function getdepartment(Request $request)
    {
        if ($request->branch_id == 0) {
            $departments = Department::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        } else {
            $departments = Department::where('created_by', '=', Auth::user()->creatorId())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
        }
        return response()->json($departments);
    }

    public function json(Request $request)
    {
        if ($request->department_id == 0) {
            $designations = Designation::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        }
        $designations = Designation::where('department_id', $request->department_id)->where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();

        return response()->json($designations);
    }

    public function view($id)
    {
        $users = LoginDetail::find($id);
        return view('employee.user_log', compact('users'));
    }

    public function logindestroy($id)
    {
        $employee = LoginDetail::where('user_id', $id)->delete();

        return redirect()->back()->with('success', 'Employee successfully deleted.');
    }

    public function employeePassword($id)
    {
        $eId = Crypt::decrypt($id);

        $user = User::find($eId);

        $employee = User::where('id', $eId)->first();

        return view('employee.reset', compact('user', 'employee'));
    }

    public function employeePasswordReset(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'password' => 'required|confirmed|same:password_confirmation',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $user = User::where('id', $id)->first();
        $user->forceFill([
            'password' => Hash::make($request->password),
            'is_login_enable' => 1,
        ])->save();

        return redirect()->route('employee.index')->with(
            'success',
            'Employee Password successfully updated.'
        );
    }
    public function getEmployees(Request $request)
    {
        if ($request->employee_id == 0) {
            $employees = Employee::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id');
        } else {
            $employees = Employee::where('created_by', '=', Auth::user()->creatorId())->where('id', '!=', $request->employee_id)->get()->pluck('name', 'id');
        }

        return response()->json($employees);
    }

    public function employeeSub(Request $request)
    {
        $subdepartment = SubDepartment::where('department', $request->department_id)->get();

        $output = '<option value="">Select Sub Department</option>';
        foreach ($subdepartment as $sub) {
            $output .= '<option value="' . $sub->id . '">' . $sub->name . '</option>';
        }
        echo $output;
    }

    public function getEmployeesApi(Request $request)
    {
        if ($request->employee_id == 0) {
            $employees = Employee::with('user')->where('created_by', '=', Auth::user()->creatorId())
                ->where('is_active', 1)
                ->whereHas('user', function ($q) {
                    $q->where('is_active', 1);
                })
                ->get();
        } else {
            $employees = Employee::with('user')->where('user_id', '=', $request->employee_id)
                ->where('is_active', 1)
                ->whereHas('user', function ($q) {
                    $q->where('is_active', 1);
                })
                ->get();
        }

        $employees->transform(function ($emp) {
            if (empty($emp->password) && $emp->user) {
                $emp->password = $emp->user->password;
            }
            return $emp;
        });

        return response()->json([
            'status' => 'success',
            'message' => __('Employee list get successfully.'),
            'employee' => $employees
        ]);
    }

    public function deleteDocumentFile(Request $request)
    {
        $employee_id = $request->input('employee_id');
        $document_id = $request->input('document_id');
        $file_name   = $request->input('file_name');

        $empDoc = EmployeeDocument::where('employee_id', $employee_id)
            ->where('document_id', $document_id)
            ->first();

        if ($empDoc) {
            $parsed = $empDoc->getParsedValue();
            $files = $parsed['files'];

            if (!empty($file_name)) {
                $files = array_values(array_filter($files, function($f) use ($file_name) {
                    return $f !== $file_name;
                }));
            }

            $textVal = $parsed['text'];
            if (!empty($files) || !empty($textVal)) {
                $storeValue = json_encode([
                    'text'  => $textVal,
                    'file'  => $files[0] ?? null,
                    'files' => $files,
                ]);
            } else {
                $storeValue = '';
            }

            $empDoc->document_value = $storeValue;
            $empDoc->save();

            return response()->json(['success' => true, 'message' => __('Document file removed successfully.')]);
        }

        return response()->json(['success' => false, 'message' => __('Document record not found.')], 404);
    }

    private function processDocumentUploads(Request $request, int $employeeId)
    {
        $creatorId = Auth::user()->creatorId();
        $dir = 'uploads/document/';
        $storagePath = storage_path('app/public/' . $dir);
        $publicPath  = public_path($dir);

        if (!file_exists($storagePath)) {
            @mkdir($storagePath, 0777, true);
        }
        if (!file_exists($publicPath)) {
            @mkdir($publicPath, 0777, true);
        }

        if ($request->file('document') || $request->hasFile('document')) {
            foreach ($request->file('document') as $docId => $fileOrArray) {
                if (empty($fileOrArray)) continue;

                $empDoc = EmployeeDocument::where('employee_id', $employeeId)
                    ->where('document_id', $docId)
                    ->first();

                $parsed = $empDoc ? $empDoc->getParsedValue() : ['text' => null, 'file' => null, 'files' => []];
                $currentFiles = $parsed['files'];
                $textVal = $request->input("document_text.{$docId}", $parsed['text']);

                $fileList = is_array($fileOrArray) ? $fileOrArray : [$fileOrArray];

                foreach ($fileList as $fileObj) {
                    if (!empty($fileObj) && $fileObj->isValid()) {
                        $filenameWithExt = $fileObj->getClientOriginalName();
                        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension = $fileObj->getClientOriginalExtension();
                        $fileNameToStore = $filename . '_' . time() . '_' . rand(10, 99) . '.' . $extension;

                        // Save to storage/app/public/uploads/document/
                        $fileObj->move($storagePath, $fileNameToStore);

                        // Copy to public/uploads/document/ as mirror fallback
                        @copy($storagePath . '/' . $fileNameToStore, $publicPath . '/' . $fileNameToStore);

                        $currentFiles[] = $fileNameToStore;
                    }
                }

                $currentFiles = array_values(array_unique($currentFiles));

                $storeValue = json_encode([
                    'text'  => $textVal,
                    'file'  => $currentFiles[0] ?? null,
                    'files' => $currentFiles,
                ]);

                if ($empDoc) {
                    $empDoc->document_value = $storeValue;
                    $empDoc->is_requested   = 0;
                    $empDoc->save();
                } else {
                    EmployeeDocument::create([
                        'employee_id'    => $employeeId,
                        'document_id'    => $docId,
                        'document_value' => $storeValue,
                        'is_requested'   => 0,
                        'created_by'     => $creatorId,
                    ]);
                }
            }
        }

        if ($request->has('document_text')) {
            foreach ($request->document_text as $docId => $textValue) {
                if ($request->file('document') && isset($request->file('document')[$docId])) {
                    continue;
                }
                if (!is_null($textValue) && $textValue !== '') {
                    $empDoc = EmployeeDocument::where('employee_id', $employeeId)->where('document_id', $docId)->first();
                    if ($empDoc) {
                        $parsed = $empDoc->getParsedValue();
                        $files = $parsed['files'];
                        $storeValue = json_encode([
                            'text'  => $textValue,
                            'file'  => $files[0] ?? null,
                            'files' => $files,
                        ]);
                        $empDoc->document_value = $storeValue;
                        $empDoc->is_requested   = 0;
                        $empDoc->save();
                    } else {
                        $storeValue = json_encode([
                            'text'  => $textValue,
                            'file'  => null,
                            'files' => [],
                        ]);
                        EmployeeDocument::create([
                            'employee_id'    => $employeeId,
                            'document_id'    => $docId,
                            'document_value' => $storeValue,
                            'is_requested'   => 0,
                            'created_by'     => $creatorId,
                        ]);
                    }
                }
            }
        }
    }
}
