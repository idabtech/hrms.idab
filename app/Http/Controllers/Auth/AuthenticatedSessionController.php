<?php

namespace App\Http\Controllers\Auth;

use App\Models\Employee;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoginDetail;
use App\Models\Utility;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use WhichBrowser\Parser;


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function __construct()
    {
        if (!file_exists(storage_path() . "/installed")) {
            header('location:install');
            die;
        }
    }

    /*protected function authenticated(Request $request, $user)
    {
        if($user->delete_status == 1)
        {
            auth()->logout();
        }

        return redirect('/check');
    }*/

    public function store(LoginRequest $request)
    {
        dd([
            'controller_store_hit'   => true,
            'current_host'          => request()->getHost(),
            'super_admin_url'       => Utility::getSuperAdminUrl(),
            'company_url'           => Utility::getCompanyUrl(),
            'is_super_admin_domain' => Utility::isSuperAdminDomain(),
        ]);
        if (session('external_login_active')) {
            if ($request->email !== session('external_login_email')) {
                return back()->withErrors([
                    'email' => 'You must login using the provided email.'
                ]);
            }
        }
        $settings = Utility::settings();
        $validation = [];
        if (isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'yes') {
            if ($settings['google_recaptcha_version'] == 'v2-checkbox') {
                $validation['g-recaptcha-response'] = 'required';
            } elseif ($settings['google_recaptcha_version'] == 'v3') {
                $token = $request->input('g-recaptcha-response', '');

                if (!empty($token)) {
                    $verified = $this->verifyRecaptchaV3($token);
                    if (!$verified) {
                        return redirect()->back()
                            ->withInput($request->only('email'))
                            ->with('error', __('reCAPTCHA verification failed. Please try again.'));
                    }
                }
            } else {
                $validation = [];
            }
        } else {
            $validation = [];
        }

        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Domain restriction enforcement
        $superAdminUrl   = Utility::getSuperAdminUrl();
        $companyUrl      = Utility::getCompanyUrl();
        $isOnAdminDomain = Utility::isSuperAdminDomain();

        if ($user->type === 'super admin' && !$isOnAdminDomain) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->back()->withInput($request->only('email'))->with('error', __('Access Denied: Super Admin can only log in through the Admin Portal: ') . $superAdminUrl);
        }

        if ($user->type !== 'super admin' && $isOnAdminDomain) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->back()->withInput($request->only('email'))->with('error', __('Access Denied: Company and Employee users must log in through the Company Portal: ') . $companyUrl);
        }

        if ($user->is_active == 0) {
            auth()->logout();
            return redirect()->back();
        }

        if ($user->is_disable == 0) {
            auth()->logout();
            return redirect()->back();
        }

        $user = \Auth::user();
        if ($user->type == 'company') {
            $plan = plan::find($user->plan);
            if ($plan) {
                if ($plan->duration != 'Lifetime') {
                    $datetime1 = new \DateTime($user->plan_expire_date);
                    $datetime2 = new \DateTime(date('Y-m-d'));

                    $interval = $datetime2->diff($datetime1);
                    $days     = $interval->format('%r%a');

                    if ($days <= 0) {
                        $user->assignplan(1);
                        session()->forget([
                            'external_login_email',
                            'external_login_active'
                        ]);
                        return redirect()->intended(RouteServiceProvider::HOME)->with('error', __('Your plan is expired.'));
                    }
                }
            }
        }

        if ($user->type == 'company') {
            $free_plan = Plan::where('price', '=', '0.0')->first();
            $plan      = Plan::find($user->plan);

            if ($user->plan != $free_plan->id) {
                if (date('Y-m-d') > $user->plan_expire_date && $plan->duration != 'Lifetime') {
                    $user->plan             = $free_plan->id;
                    $user->plan_expire_date = null;
                    $user->save();

                    $users     = User::where('created_by', '=', \Auth::user()->creatorId())->get();
                    $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get();

                    if ($free_plan->max_users == -1) {
                        foreach ($users as $user) {
                            $user->is_active = 1;
                            $user->save();
                        }
                    } else {
                        $userCount = 0;
                        foreach ($users as $user) {
                            $userCount++;
                            if ($userCount <= $free_plan->max_users) {
                                $user->is_active = 1;
                                $user->save();
                            } else {
                                $user->is_active = 0;
                                $user->save();
                            }
                        }
                    }


                    if ($free_plan->max_employees == -1) {
                        foreach ($employees as $employee) {
                            $employee->is_active = 1;
                            $employee->save();
                        }
                    } else {
                        $employeeCount = 0;
                        foreach ($employees as $employee) {
                            $employeeCount++;
                            if ($employeeCount <= $free_plan->max_customers) {
                                $employee->is_active = 1;
                                $employee->save();
                            } else {
                                $employee->is_active = 0;
                                $employee->save();
                            }
                        }
                    }
                    session()->forget([
                        'external_login_email',
                        'external_login_active'
                    ]);
                    return redirect()->route('dashboard')->with('error', 'Your plan expired limit is over, please upgrade your plan');
                }
            }
        }


        if ($user->type != 'company' && $user->type != 'super admin') {
            // $ip = '49.36.83.154'; // This is static ip address
            $ip = $_SERVER['REMOTE_ADDR']; // your ip address here
            $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));

            $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
            if ($whichbrowser->device->type == 'bot') {
                return;
            }
            $referrer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;

            /* Detect extra details about the user */
            $query['browser_name'] = $whichbrowser->browser->name ?? null;
            $query['os_name'] = $whichbrowser->os->name ?? null;
            $query['browser_language'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $query['device_type'] = Utility::get_device_type($_SERVER['HTTP_USER_AGENT']);
            $query['referrer_host'] = !empty($referrer['host']);
            $query['referrer_path'] = !empty($referrer['path']);


            isset($query['timezone']) ? date_default_timezone_set($query['timezone']) : '';


            $json = json_encode($query);

            $login_detail = new LoginDetail();
            $login_detail->user_id = Auth::user()->id;
            $login_detail->ip = $ip;
            $login_detail->date = date('Y-m-d H:i:s');
            $login_detail->Details = $json;
            $login_detail->created_by = \Auth::user()->creatorId();
            $login_detail->save();
        }

        // $user->last_login = date('Y-m-d H:i:s');
        // $user->save();
        session()->forget([
            'external_login_email',
            'external_login_active'
        ]);
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function showLoginForm($lang = '')
    {
        if ($lang == '') {
            $lang = \App\Models\Utility::getValByName('default_language');
        }
        \App::setLocale($lang);

        return view('auth.login', compact('lang'));
    }

    public function showLinkRequestForm($lang = '')
    {
        if ($lang == '') {
            $lang = \App\Models\Utility::getValByName('default_language');
        }

        \App::setLocale($lang);

        return view('auth.forgot-password', compact('lang'));
    }
    public function storeLinkRequestForm(Request $request)
    {
        $settings = Utility::settings();
        if (isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'yes') {
            $validation['g-recaptcha-response'] = 'required';
        } else {
            $validation = [];
        }

        $validator = Validator::make(
            $request->all(),
            $validation
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        try {

            $status = Password::sendResetLink(
                $request->only('email')
            );

            return $status == Password::RESET_LINK_SENT
                ? back()->with('status', __($status))
                : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        } catch (\Exception $e) {

            return redirect()->back()->withErrors('E-Mail has been not sent due to SMTP configuration');
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Verify a reCAPTCHA v3 token directly with Google.
     * Uses the super admin's secret key (that's where keys are stored).
     * Returns true if verified OK, false if failed.
     */
    private function verifyRecaptchaV3(string $token): bool
    {
        try {
            $superAdmin = \App\Models\User::where('type', 'super admin')->first();
            $settings   = $superAdmin
                ? Utility::fetchSettings($superAdmin->id)
                : Utility::settings();

            $secretKey = $settings['google_recaptcha_secret'] ?? '';

            if (empty($secretKey)) {
                \Illuminate\Support\Facades\Log::warning('reCAPTCHA: secret key empty, skipping verification');
                return true; // no key configured — don't block login
            }

            $context = stream_context_create([
                'http' => [
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query(['secret' => $secretKey, 'response' => $token]),
                    'timeout' => 10,
                ],
            ]);

            $raw      = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
            $response = json_decode($raw);

            if (!$response || !$response->success) {
                $errors = implode(',', (array)($response->{'error-codes'} ?? []));
                \Illuminate\Support\Facades\Log::warning('reCAPTCHA failed: ' . $errors);
                return false;
            }

            // v3: reject low-confidence scores (bots score near 0, humans near 1)
            if (isset($response->score) && $response->score < 0.5) {
                \Illuminate\Support\Facades\Log::warning('reCAPTCHA score too low: ' . $response->score);
                return false;
            }

            return true;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('reCAPTCHA exception: ' . $e->getMessage());
            return true; // network failure — don't block real users
        }
    }
}
