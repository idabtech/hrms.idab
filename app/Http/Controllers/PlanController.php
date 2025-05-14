<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Models\Utility;
use App\Models\PlanRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    private $origin_country;
    private $selected_country;

    public function __construct()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $token = env('IPINFO_API_TOKEN');

        // Cache country per IP for 12 hours
        $this->origin_country = Cache::remember("geo_ip_country_{$ip}", now()->addHours(12), function () use ($ip, $token) {
            return $this->getCountryWithRetryLoop($ip, $token);
        });

        // Set country group for plan selection
        if ($this->origin_country === 'IN') {
            $this->selected_country = 1;
        } elseif (in_array($this->origin_country, ['GB', 'IE'])) {
            $this->selected_country = 2;
        } else {
            $this->selected_country = 3;
        }
    }

    private function getCountryWithRetryLoop($ip, $token)
    {
        $attempt = 0;

        while (true) {
            $attempt++;

            // 1. Try ip-api.com
            $country = $this->getCountryFromIpApi($ip);
            if ($country) {
                return $country;
            }

            // 2. Try ipinfo.io
            $country = $this->getCountryFromIpInfo($ip, $token);
            if ($country) {
                return $country;
            }

            // Avoid tight loop (optional: add a small sleep)
            sleep(1); // Wait 1 second before retrying
        }
    }

    private function getCountryFromIpApi($ip)
    {
        $json = @file_get_contents("http://ip-api.com/json/{$ip}");
        if ($json) {
            $data = json_decode($json);
            if (isset($data->countryCode) && $data->status === 'success') {
                return $data->countryCode;
            }
        }
        return null;
    }

    private function getCountryFromIpInfo($ip, $token)
    {
        $json = @file_get_contents("http://ipinfo.io/{$ip}?token={$token}");
        if ($json) {
            $data = json_decode($json);
            if (isset($data->country)) {
                return $data->country;
            }
        }
        return null;
    }

    public function index()
    {
        if (Auth::user()->can('Manage Plan')) {
            $plans = Plan::get();
            $admin_payment_setting = Utility::getAdminPaymentSetting();
            // dd($admin_payment_setting);
            $selected_country = $this->selected_country;

            return view('plan.index', compact('plans', 'admin_payment_setting', 'selected_country'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (Auth::user()->can('Create Plan')) {
            $arrDuration = Plan::$arrDuration;
            $arrPerUserOrCompany = Plan::$arrPerUserOrCompany;
            $arrCurrency = Plan::$arrCurrency;
            $selected_country = $this->selected_country;
            $arrPms = Plan::$arrPms;

            return view('plan.create', compact('arrDuration', 'arrPerUserOrCompany', 'arrCurrency', 'selected_country', 'arrPms'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        if (Auth::user()->can('Create Plan')) {
            $admin_payment_setting = Utility::getAdminPaymentSetting();
            if (!empty($admin_payment_setting) && ($admin_payment_setting['is_stripe_enabled'] == 'on' || $admin_payment_setting['is_paypal_enabled'] == 'on' || $admin_payment_setting['is_paystack_enabled'] == 'on' || $admin_payment_setting['is_flutterwave_enabled'] == 'on' || $admin_payment_setting['is_razorpay_enabled'] == 'on' || $admin_payment_setting['is_mercado_enabled'] == 'on' || $admin_payment_setting['is_paytm_enabled'] == 'on' || $admin_payment_setting['is_mollie_enabled'] == 'on' || $admin_payment_setting['is_paypal_enabled'] == 'on' || $admin_payment_setting['is_skrill_enabled'] == 'on' || $admin_payment_setting['is_coingate_enabled'] == 'on')) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|unique:plans',
                        'price' => 'required|numeric|min:0',
                        'per_user_or_company' => 'required',
                        'set_currency' => 'required',
                        'pms' => 'required',
                        'duration' => 'required',
                        'max_users' => 'required|numeric',
                        'max_employees' => 'required|numeric',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $post = $request->all();

                if (Plan::create($post)) {
                    return redirect()->back()->with('success', __('Plan Successfully created.'));
                } else {
                    return redirect()->back()->with('error', __('Something is wrong.'));
                }
            } else {
                return redirect()->back()->with('error', __('Please set stripe/paypal api key & secret key for add new plan'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit($plan_id)
    {
        if (Auth::user()->can('Edit Plan')) {
            $arrDuration = Plan::$arrDuration;
            $arrPerUserOrCompany = Plan::$arrPerUserOrCompany;
            $arrCurrency = Plan::$arrCurrency;
            $selected_country = $this->selected_country;
            $plan = Plan::find($plan_id);
            $arrPms = Plan::$arrPms;


            return view('plan.edit', compact('plan', 'arrDuration', 'arrPerUserOrCompany', 'arrCurrency', 'selected_country', 'arrPms'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, $plan_id)
    {
        if (Auth::user()->can('Edit Plan')) {
            $admin_payment_setting = Utility::getAdminPaymentSetting();
            if (!empty($admin_payment_setting) && ($admin_payment_setting['is_stripe_enabled'] == 'on' || $admin_payment_setting['is_paypal_enabled'] == 'on' || $admin_payment_setting['is_paystack_enabled'] == 'on' || $admin_payment_setting['is_flutterwave_enabled'] == 'on' || $admin_payment_setting['is_razorpay_enabled'] == 'on' || $admin_payment_setting['is_mercado_enabled'] == 'on' || $admin_payment_setting['is_paytm_enabled'] == 'on' || $admin_payment_setting['is_mollie_enabled'] == 'on' || $admin_payment_setting['is_paypal_enabled'] == 'on' || $admin_payment_setting['is_skrill_enabled'] == 'on' || $admin_payment_setting['is_coingate_enabled'] == 'on')) {
                $plan = Plan::find($plan_id);
                if (!empty($plan)) {
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'name' => 'required|unique:plans,name,' . $plan_id,
                            'duration' => 'required',
                            'max_users' => 'required|numeric',
                            'max_employees' => 'required|numeric',
                        ]
                    );
                    if ($validator->fails()) {
                        $messages = $validator->getMessageBag();

                        return redirect()->back()->with('error', $messages->first());
                    }

                    $post = $request->all();

                    if ($plan->update($post)) {
                        return redirect()->back()->with('success', __('Plan successfully updated.'));
                    } else {
                        return redirect()->back()->with('error', __('Something is wrong.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('Plan not found.'));
                }
            } else {
                return redirect()->back()->with('error', __('Please set stripe/paypal api key & secret key for add new plan'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $user = User::where('id', '=', $user->id)->first();
        $user->requested_plan = "0";
        $user->save();

        $plan = Plan::findOrFail($id);
        PlanRequest::where('plan_id', $plan->id)->where('user_id', '=', $user->id)->delete();

        return redirect()->route('plans.index')->with('success', 'Plan request successfully deleted.');
    }

    public function plan_request($code)
    {
        $objUser = Auth::user();

        $plan_id = Crypt::decrypt($code);
        $plan = Plan::find($plan_id);

        $plan_request_check_user = PlanRequest::where('user_id', '=', $objUser->id)->first();

        if ($plan_request_check_user) {
            return redirect()->back()->with('error', __('you already request sended for plan.'));
        } else {
            $planRequest = new PlanRequest();
            $planRequest['user_id'] = $objUser->id;
            $planRequest['plan_id'] = $plan->id;
            $planRequest['duration'] = $plan->duration;
            $planRequest->save();

            $objUser['requested_plan'] = $plan->id;
            $objUser->save();

            return redirect()->back()->with('success', __('Plan request successfully sended.'));
        }
    }


    public function userPlan(Request $request)
    {
        if (Auth::user()->can('Buy Plan')) {
            $objUser = Auth::user();
            $planID = Crypt::decrypt($request->code);
            $plan = Plan::find($planID);
            if ($plan) {
                if ($plan->price <= 0) {
                    $objUser->assignPlan($plan->id);

                    return redirect()->route('plans.index')->with('success', __('Plan successfully activated.'));
                } else {
                    return redirect()->back()->with('error', __('Something is wrong.'));
                }
            } else {
                return redirect()->back()->with('error', __('Plan not found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function OrderDestroy(Request $request, $id)
    {
        if (Auth::user()->type == 'super admin') {
            $order = Order::find($id);
            $file = $order->receipt;
            if (File::exists(storage_path('uploads/order/' . $file))) {
                File::delete(storage_path('uploads/order/' . $file));
            }
            $order->delete();
            return redirect()->route('order.index')->with('success', __('Order successfully deleted.'));
        }
    }

    public function PlanTrial($id)
    {
        if (Auth::user()->can('Buy Plan') && Auth::user()->type != 'super admin') {
            if (Auth::user()->is_trial_done == false) {
                try {
                    $id = Crypt::decrypt($id);
                } catch (\Throwable $th) {
                    return redirect()->back()->with('error', __('Plan Not Found.'));
                }
                $plan = Plan::find($id);
                $user = User::where('id', Auth::user()->id)->first();
                $currentDate = date('Y-m-d');
                $numberOfDaysToAdd = $plan->trial_days;
                $newDate = date('Y-m-d', strtotime($currentDate . ' + ' . $numberOfDaysToAdd . ' days'));

                if (!empty($plan->trial) == 1) {

                    $user->assignPlan($plan->id);

                    $user->trial_plan = $id;
                    $user->trial_expire_date = $newDate;
                    $user->save();
                }
                return redirect()->back()->with('success', 'Your trial has been started.');
            } else {
                return redirect()->back()->with('error', __('Your Plan trial already done.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function planDisable(Request $request)
    {
        $userPlan = User::where('plan', $request->id)->first();
        if ($userPlan != null) {
            return response()->json(['error' => __('The company has subscribed to this plan, so it cannot be disabled.')]);
        }

        Plan::where('id', $request->id)->update(['is_disable' => $request->is_disable]);

        if ($request->is_disable == 1) {
            return response()->json(['success' => __('Plan successfully enable.')]);
        } else {
            return response()->json(['success' => __('Plan successfully disable.')]);
        }
    }
}
