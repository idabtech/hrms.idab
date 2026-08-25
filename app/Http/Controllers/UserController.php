<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Invoice;
use App\Mail\UserCreate;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Utility;
use App\Models\GenerateOfferLetter;
use App\Models\JoiningLetter;
use App\Models\ExperienceCertificate;
use App\Models\NOC;
use App\Models\Webhook;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Lab404\Impersonate\Impersonate;;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Routing\Controller as Controller;

class UserController extends Controller
{

    public function __construct()
    {
        // $this->middleware('2fa');
    }

    public function index()
    {
        if (\Auth::user()->can('Manage User')) {
            $user = \Auth::user();
            if (\Auth::user()->type == 'super admin') {
                $users = User::where('created_by', '=', $user->creatorId())->where('type', '=', 'company')->with('currentPlan')->get();
                $CountUser = User::where('created_by')->get();
            } else {
                $users = User::where('created_by', '=', $user->creatorId())->where('type', '!=', 'employee')->get();
            }

            return view('user.index', compact('users'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create User')) {
            $user  = \Auth::user();
            $roles = Role::where('created_by', '=', $user->creatorId())->where('name', '!=', 'employee')->get()->pluck('name', 'id');
            $roles->prepend('Select Role', '');

            return view('user.create', compact('roles'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create User')) {
            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->where('created_by', \Auth::user()->creatorId())->first();

            // new company default language
            if ($default_language == null) {
                $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
            }

            $validator        = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|unique:users',
                    // 'password' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if (!empty($request->password_switch) && $request->password_switch == 'on') {
                $validator = \Validator::make(
                    $request->all(),
                    ['password' => 'required|min:6']
                );

                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }
            }

            do {
                $code = rand(100000, 999999);
            } while (User::where('referral_code', $code)->exists());

            if (\Auth::user()->type == 'super admin') {
                $date = date("Y-m-d H:i:s");
                $userpassword = $request->input('password');
                $passcode = random_int(100000, 999999);
                $user = User::create(
                    [
                        'name' => $request['name'],
                        'email' => $request['email'],
                        'is_login_enable' => !empty($request->password_switch) && $request->password_switch == 'on' ? 1 : 0,
                        'password' => !empty($userpassword) ? Hash::make($userpassword) : null,
                        'type' => 'company',
                        'plan' => $plan = Plan::where('price', '<=', 0)->first()->id,
                        'lang' => !empty($default_language) ? $default_language->value : 'en',
                        'referral_code' => $code,
                        'created_by' => \Auth::user()->id,
                        'email_verified_at' => $date,
                        'passcode' => $passcode
                    ]
                );

                $user->assignRole('Company');
                // $user->userDefaultData();
                $user->userDefaultDataRegister($user->id);
                GenerateOfferLetter::defaultOfferLetterRegister($user->id);
                ExperienceCertificate::defaultExpCertificatRegister($user->id);
                JoiningLetter::defaultJoiningLetterRegister($user->id);
                NOC::defaultNocCertificateRegister($user->id);
                Utility::jobStage($user->id);
                $role_r = Role::findById(2);

                //create company default roles
                Utility::MakeRole($user->id);
            } else {
                $objUser    = \Auth::user()->creatorId();
                $objUser = User::find($objUser);
                $total_user = $objUser->countUsers();
                $plan       = Plan::find($objUser->plan);
                $userpassword = $request->input('password');

                if ($total_user < $plan->max_users || $plan->max_users == -1) {

                    $role_r = Role::findById($request->role);
                    $date = date("Y-m-d H:i:s");
                    $passcode = random_int(100000, 999999);

                    $user   = User::create(
                        [
                            'name' => $request['name'],
                            'email' => $request['email'],
                            'is_login_enable' => !empty($request->password_switch) && $request->password_switch == 'on' ? 1 : 0,
                            'password' => !empty($userpassword) ? Hash::make($userpassword) : null,
                            'type' => $role_r->name,
                            'lang' => !empty($default_language) ? $default_language->value : 'en',
                            'created_by' => \Auth::user()->creatorId(),
                            'email_verified_at' => $date,
                            'passcode' => $passcode
                        ]
                    );
                    $user->assignRole($role_r);

                    // Auto-create a minimal Employee record so admin staff can clock in/out
                    $this->createStaffEmployeeRecord($user);
                } else {
                    return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
                }
            }

            $setings = Utility::settings();


            if ($setings['new_user'] == 1) {

                $uArr = [
                    'email' => $user->email,
                    'password' => $request->password,
                    'passcode' => $user->passcode
                ];

                $resp = Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr);

                return redirect()->route('user.index')->with('success', __('User successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
            return redirect()->route('user.index')->with('success', __('User successfully created.'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function show(User $user)
    {
        return view('profile.index');
    }

    public function edit($id)
    {
        if (\Auth::user()->can('Edit User')) {
            $user  = User::find($id);
            $roles = Role::where('created_by', '=', $user->creatorId())->where('name', '!=', 'employee')->get()->pluck('name', 'id');
            $roles->prepend('Select Role', '');

            return view('user.edit', compact('user', 'roles'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'unique:users,email,' . $id,
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if (\Auth::user()->type == 'super admin') {
            $user  = User::findOrFail($id);
            $input = $request->all();
            $user->fill($input)->save();
        } else {
            $user = User::findOrFail($id);

            $role          = Role::findById($request->role);
            $input         = $request->all();
            $input['type'] = $role->name;
            $user->fill($input)->save();

            $user->assignRole($role);
        }

        $this->updateStaffEmployeeRecord($user);
        return redirect()->route('user.index')->with('success', 'User successfully updated.');
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('Delete User')) {
            $user = User::findOrFail($id);
            $sub_employee = Employee::where('created_by', $user->id)->delete();
            $sub_user = User::where('created_by', $user->id)->delete();
            $user->delete();

            return redirect()->route('user.index')->with('success', 'User successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bulkDestroy(Request $request)
    {
        if (\Auth::user()->type === 'super admin' || \Auth::user()->can('Delete User')) {
            $ids = $request->ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            $ids = array_filter(array_map('intval', (array) $ids));

            if (empty($ids)) {
                return redirect()->back()->with('error', __('Please select at least one company to delete.'));
            }

            $count = 0;
            foreach ($ids as $id) {
                $user = User::find($id);
                if ($user && $user->id != \Auth::id()) {
                    Employee::where('created_by', $user->id)->delete();
                    User::where('created_by', $user->id)->delete();
                    $user->delete();
                    $count++;
                }
            }

            return redirect()->route('user.index')->with('success', __(':count companies deleted successfully.', ['count' => $count]));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function profile()
    {
        $userDetail = \Auth::user();

        $google2fa = new Google2FA();

        $google2fa_url = $google2fa->getQRCodeInline(
            config('app.name'),
            $userDetail['email'],
            $userDetail['google2fa_secret'],
        );
        $userDetail['google2fa_url'] = $google2fa_url;
        return view('user.profile')->with('userDetail', $userDetail);
    }

    public function editprofile(Request $request)
    {
        $userDetail = \Auth::user();
        $user       = User::findOrFail($userDetail['id']);

        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required|max:120',
                'email' => 'required|email|unique:users,email,' . $userDetail['id'],
                // 'profile' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if ($request->hasFile('profile')) {
            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;


            $dir        = 'uploads/avatar';

            $image_path = $dir . $userDetail['avatar'];
            if (File::exists($image_path)) {
                File::delete($image_path);
            }
            $url = '';
            $path = Utility::upload_file($request, 'profile', $fileNameToStore, $dir, []);

            if ($path['flag'] == 1) {
                $url = $path['url'];
            } else {
                return redirect()->route('profile', \Auth::user()->id)->with('error', __($path['msg']));
            }
        }

        if (!empty($request->profile)) {
            $user['avatar'] = $fileNameToStore;
        }
        $user['name']  = $request['name'];
        $user['email'] = $request['email'];
        $user->save();

        if (\Auth::user()->type == 'employee') {
            $employee        = Employee::where('user_id', $user->id)->first();
            $employee->email = $request['email'];
            $employee->save();
        }

        return redirect()->back()->with(
            'success',
            'Profile successfully updated.'
        );
    }

    public function LoginManage($id)
    {
        $eId        = \Crypt::decrypt($id);
        $user = User::find($eId);
        if ($user->is_login_enable == 1) {
            $user->is_login_enable = 0;
            $user->save();
            return redirect()->back()->with('success', 'User login disable successfully.');
        } else {
            $user->is_login_enable = 1;
            $user->save();
            return redirect()->back()->with('success', 'User login enable successfully.');
        }
    }

    public function userPassword($id)
    {
        $eId        = \Crypt::decrypt($id);

        $user = User::find($eId);

        $employee = User::where('id', $eId)->first();

        return view('user.reset', compact('user', 'employee'));
    }

    public function userPasswordReset(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'password' => 'required|confirmed|same:password_confirmation',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $user                 = User::where('id', $id)->first();
        $user->forceFill([
            'password' => Hash::make($request->password),
            'is_login_enable' => 1,
        ])->save();

        return redirect()->route('user.index')->with(
            'success',
            'User Password successfully updated.'
        );
    }


    public function updatePassword(Request $request)
    {
        if (\Auth::Check()) {
            $request->validate(
                [
                    'current_password' => 'required',
                    'new_password' => 'required|min:6',
                    'confirm_password' => 'required|same:new_password',
                ]
            );
            $objUser          = Auth::user();
            $request_data     = $request->All();
            $current_password = $objUser->password;
            if (Hash::check($request_data['current_password'], $current_password)) {
                $user_id            = Auth::User()->id;
                $obj_user           = User::find($user_id);
                $obj_user->password = Hash::make($request_data['new_password']);;
                $obj_user->save();

                return redirect()->route('profile', $objUser->id)->with('success', __('Password successfully updated.'));
            } else {
                return redirect()->route('profile', $objUser->id)->with('error', __('Please enter correct current password.'));
            }
        } else {
            return redirect()->route('profile', \Auth::user()->id)->with('error', __('Something is wrong.'));
        }
    }
    public function updatePasscode(Request $request)
    {
        if (Auth::Check()) {
            $request->validate(
                [
                    'current_passcode' => 'required',
                    'new_passcode' => 'required|min:6',
                    'confirm_passcode' => 'required|same:new_passcode',
                ]
            );
            $objUser          = Auth::user();
            $request_data     = $request->All();
            $current_passcode = $objUser->passcode;
            if ($request_data['current_passcode'] == $current_passcode || is_null($current_passcode)) {
                $user_id            = Auth::User()->id;
                $obj_user           = User::find($user_id);
                $obj_user->passcode =$request_data['new_passcode'];
                $obj_user->save();

                return redirect()->route('profile', $objUser->id)->with('success', __('Passcode successfully updated.'));
            } else {
                return redirect()->route('profile', $objUser->id)->with('error', __('Please enter correct current passcode.'));
            }
        } else {
            return redirect()->route('profile', \Auth::user()->id)->with('error', __('Something is wrong.'));
        }
    }


    public function upgradePlan($user_id)
    {
        $user = User::find($user_id);

        $plans = Plan::where('is_disable', 1)->get();

        return view('user.plan', compact('user', 'plans'));
    }

    public function activePlan($user_id, $plan_id)
    {
        $admin_payment_setting = Utility::getAdminPaymentSetting();
        $user       = User::find($user_id);
        $assignPlan = $user->assignPlan($plan_id, $user_id);
        $plan       = Plan::find($plan_id);
        if ($assignPlan['is_success'] == true && !empty($plan)) {
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            Order::create(
                [
                    'order_id' => $orderID,
                    'name' => null,
                    'card_number' => null,
                    'card_exp_month' => null,
                    'card_exp_year' => null,
                    'plan_name' => $plan->name,
                    'plan_id' => $plan->id,
                    'price' => $plan->price,
                    'price_currency' => !empty($admin_payment_setting['currency']) ? $admin_payment_setting['currency'] : '$',
                    'txn_id' => '',
                    'payment_status' => 'succeeded',
                    'receipt' => null,
                    'user_id' => $user->id,
                ]
            );

            return redirect()->back()->with('success', 'Plan successfully upgraded.');
        } else {
            return redirect()->back()->with('error', 'Plan fail to upgrade.');
        }
    }

    public function notificationSeen($user_id)
    {
        Notification::where('user_id', '=', $user_id)->update(['is_read' => 1]);

        return response()->json(['is_success' => true], 200);
    }

    public function LoginWithCompany(Request $request, User $user, $id)
    {
        $user = User::find($id);
        if ($user && auth()->check() && auth()->user()->type === 'super admin') {
            $settings = Utility::settings();
            $securityOn = ($settings['login_as_company_security'] ?? 'on') === 'on';

            if ($securityOn) {
                return view('user.modal_login_company_otp', ['company' => $user]);
            }

            $companyUrl = Utility::getCompanyUrl();
            $companyUrl = rtrim($companyUrl, '/');

            $secret = config('services.sso.secret', 'idab_default_sso_secret');
            if (empty($secret)) {
                $secret = 'idab_default_sso_secret';
            }

            $payloadData = [
                'email' => $user->email,
                'ts'    => now()->timestamp,
            ];
            $payload   = base64_encode(json_encode($payloadData));
            $signature = hash_hmac('sha256', $payload, $secret);

            $redirectUrl = $companyUrl . '/external-login?payload=' . urlencode($payload) . '&signature=' . urlencode($signature);
            return redirect($redirectUrl);
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    private function sendCompanyLoginOtpInternal(User $company)
    {
        $otpCode = rand(100000, 999999);
        session([
            'company_login_otp_' . $company->id => [
                'code'       => (string) $otpCode,
                'expires_at' => now()->addMinutes(5)->timestamp,
            ]
        ]);

        $placeholders = [
            'company_name'        => $company->name,
            'otp_code'            => $otpCode,
            'otp_expires_minutes' => 5,
            'app_name'            => config('app.name', 'HRMS'),
        ];

        try {
            Utility::sendEmailTemplate('login_as_company_otp', [$company->email], $placeholders);
        } catch (\Exception $e) {
            \Log::error('Company Login OTP Mail Error: ' . $e->getMessage());
        }
    }

    public function sendCompanyLoginOtp(Request $request, $id)
    {
        $company = User::find($id);
        if (!$company) {
            return response()->json(['status' => 'error', 'message' => __('Company not found.')], 404);
        }

        $this->sendCompanyLoginOtpInternal($company);
        return response()->json(['status' => 'success', 'message' => __('A new OTP has been sent to the company registered email.')]);
    }

    public function verifyCompanyLoginOtp(Request $request, $id)
    {
        $company = User::find($id);
        if (!$company) {
            return response()->json(['status' => 'error', 'message' => __('Company not found.')], 404);
        }

        $otpCode = $request->input('otp_code');
        $sessionData = session('company_login_otp_' . $company->id);

        if (!$sessionData || !isset($sessionData['code'], $sessionData['expires_at'])) {
            return response()->json(['status' => 'error', 'message' => __('No active OTP found. Please request a new one.')], 422);
        }

        if (now()->timestamp > $sessionData['expires_at']) {
            return response()->json(['status' => 'error', 'message' => __('OTP has expired. Please click Resend OTP.')], 422);
        }

        if ((string)$otpCode !== (string)$sessionData['code']) {
            return response()->json(['status' => 'error', 'message' => __('Invalid OTP code. Please check your email and try again.')], 422);
        }

        session()->forget('company_login_otp_' . $company->id);

        $companyUrl = Utility::getCompanyUrl();
        $companyUrl = rtrim($companyUrl, '/');

        $secret = config('services.sso.secret', 'idab_default_sso_secret');
        if (empty($secret)) {
            $secret = 'idab_default_sso_secret';
        }

        $payloadData = [
            'email' => $company->email,
            'ts'    => now()->timestamp,
        ];
        $payload   = base64_encode(json_encode($payloadData));
        $signature = hash_hmac('sha256', $payload, $secret);

        $redirectUrl = $companyUrl . '/external-login?payload=' . urlencode($payload) . '&signature=' . urlencode($signature);

        return response()->json([
            'status'       => 'success',
            'message'      => __('OTP verified successfully.'),
            'redirect_url' => $redirectUrl,
        ]);
    }

    public function ExitCompany(Request $request)
    {
        $superAdminUrl = Utility::getSuperAdminUrl();
        $superAdminUrl = rtrim($superAdminUrl, '/');

        if (\Auth::check() && method_exists(\Auth::user(), 'leaveImpersonation')) {
            \Auth::user()->leaveImpersonation($request->user());
        } else {
            \Auth::logout();
        }

        return redirect($superAdminUrl . '/login');
    }

    public function CompnayInfo($id)
    {
        if (!empty($id)) {
            $data = $this->userCounter($id);
            if ($data['is_success']) {
                $users_data = $data['response']['users_data'];
                return view('user.companyinfo', compact('id', 'users_data'));
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function UserUnable(Request $request)
    {
        if (!empty($request->id) && !empty($request->company_id)) {
            if ($request->name == 'user') {
                User::where('id', $request->id)->update(['is_disable' => $request->is_disable]);
                $data = $this->userCounter($request->company_id);
            }
            if ($data['is_success']) {
                $users_data = $data['response']['users_data'];
            }
            if ($request->is_disable == 1) {

                return response()->json(['success' => __('Successfully Unable.'), 'users_data' => $users_data]);
            } else {
                return response()->json(['success' => __('Successfully Disable.'), 'users_data' => $users_data]);
            }
        }
        return response()->json('error');
    }

    public function userCounter($id)
    {
        $response = [];

        if (!empty($id)) {
            $users = User::where('created_by', $id)
                ->selectRaw('COUNT(*) as total_users, SUM(CASE WHEN is_disable = 0 THEN 1 ELSE 0 END) as disable_users, SUM(CASE WHEN is_disable = 1 THEN 1 ELSE 0 END) as active_users')
                ->first();

            $users_data = [
                'user_id'    => !empty($id) ? $id : 0,
                'total_users'    => !empty($users->total_users) ? $users->total_users : 0,
                'disable_users'  => !empty($users->disable_users) ? $users->disable_users : 0,
                'active_users'   => !empty($users->active_users) ? $users->active_users : 0,
            ];

            $response['users_data'] = $users_data;

            return [
                'is_success' => true,
                'response'   => $response,
            ];
        }

        return [
            'is_success' => false,
            'error'      => 'User ID is invalid.',
        ];
    }

    /**
     * Auto-create a minimal Employee record for admin staff users (hr, manager, etc.)
     * so they can use the clock in/out attendance system.
     */
    private function createStaffEmployeeRecord(User $user): void
    {
        // Skip if an employee record already exists for this user
        if (Employee::where('user_id', $user->id)->exists()) {
            return;
        }

        // Generate a unique employee_id number
        $latest = Employee::where('created_by', $user->created_by)->latest('id')->first();
        $employeeIdNumber = $latest ? ($latest->employee_id + 1) : 1;

        // Get default branch/department/designation if available (nullable, so fine if none)
        $branch      = Branch::where('created_by', $user->created_by)->first();
        $department  = Department::where('created_by', $user->created_by)->first();
        $designation = Designation::where('created_by', $user->created_by)->first();

        Employee::create([
            'user_id'        => $user->id,
            'name'           => $user->name,
            'last_name'      => '',
            'email'          => $user->email,
            'employee_id'    => $employeeIdNumber,
            'branch_id'      => $branch ? $branch->id : null,
            'department_id'  => $department ? $department->id : null,
            'designation_id' => $designation ? $designation->id : null,
            'company_doj'    => date('Y-m-d'),
            'created_by'     => $user->created_by,
            'is_admin_staff' => 1,
        ]);
    }

    private function updateStaffEmployeeRecord(User $user): void
    {
        // Skip if an employee record already exists for this user
        $employee = Employee::where('user_id', $user->id)->first();
        if ($employee) {
            $employee->name = $user->name;
            $employee->email = $user->email;
            $employee->save();
        } else {
            // Generate a unique employee_id number
            $latest = Employee::where('created_by', $user->created_by)->latest('id')->first();
            $employeeIdNumber = $latest ? ($latest->employee_id + 1) : 1;

            // Get default branch/department/designation if available (nullable, so fine if none)
            $branch      = Branch::where('created_by', $user->created_by)->first();
            $department  = Department::where('created_by', $user->created_by)->first();
            $designation = Designation::where('created_by', $user->created_by)->first();

            Employee::create([
                'user_id'        => $user->id,
                'name'           => $user->name,
                'last_name'      => '',
                'email'          => $user->email,
                'employee_id'    => $employeeIdNumber,
                'branch_id'      => $branch ? $branch->id : null,
                'department_id'  => $department ? $department->id : null,
                'designation_id' => $designation ? $designation->id : null,
                'company_doj'    => date('Y-m-d'),
                'created_by'     => $user->created_by,
                'is_admin_staff' => 1,
            ]);
        }

    }
}
