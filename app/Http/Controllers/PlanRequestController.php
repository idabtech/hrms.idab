<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Models\PlanRequest;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (Auth::user()->type == 'super admin' || (Auth::user()->isSuperAdminSideUser() && Auth::user()->can('Manage Plan Request'))) {
            $plan_requests = PlanRequest::all();

            return view('plan_request.index', compact('plan_requests'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /*
     *@plan_id = Plan ID encoded
    */
    public function requestView($plan_id)
    {
        if (Auth::user()->type != 'super admin') {
            $planID = \Illuminate\Support\Facades\Crypt::decrypt($plan_id);
            $plan   = Plan::find($planID);

            if (!empty($plan)) {
                return view('plan_request.show', compact('plan'));
            } else {
                return redirect()->back()->with('error', __('Something went wrong.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    /*
     * @plan_id = Plan ID encoded
     * @duration = what duration is selected by user while request
    */
    public function userRequest($plan_id)
    {
        $objUser = Auth::user();

        if ($objUser->requested_plan == 0) {
            $planID = \Illuminate\Support\Facades\Crypt::decrypt($plan_id);
            $plan = Plan::where('id', $planID)->first();
            if (!empty($planID)) {
                PlanRequest::create([
                    'user_id' => $objUser->id,
                    'plan_id' => $planID,
                    'duration' => $plan->duration
                ]);

                // Update User Table

                $objUser['requested_plan'] = $planID;
                $objUser->update();

                return redirect()->back()->with('success', __('Request Send Successfully.'));
            } else {
                return redirect()->back()->with('error', __('Something went wrong.'));
            }
        } else {
            return redirect()->back()->with('error', __('You already send request to another plan.'));
        }
    }

    /*
     * @id = Project ID
     * @response = 1(accept) or 0(reject)
    */
    public function acceptRequest($id, $response)
    {
        $user = Auth::user();
        if ($user->type == 'super admin' || $user->isSuperAdminSideUser()) {
            if ($response == 1 && $user->type !== 'super admin' && !$user->can('Approve Plan Request')) {
                return redirect()->back()->with('error', __('Permission Denied. You do not have permission to approve plan requests.'));
            }
            if ($response == 0 && $user->type !== 'super admin' && !$user->can('Delete Plan Request')) {
                return redirect()->back()->with('error', __('Permission Denied. You do not have permission to reject plan requests.'));
            }

            $payment_setting = Utility::getAdminPaymentSetting();
            $plan_request = PlanRequest::find($id);
            if (!empty($plan_request)) {
                $userTarget = User::find($plan_request->user_id);

                if ($response == 1) {
                    $userTarget->requested_plan = $plan_request->plan_id;
                    $userTarget->plan           = $plan_request->plan_id;
                    $userTarget->requested_plan = '0';
                    $userTarget->save();

                    $plan       = Plan::find($plan_request->plan_id);
                    $assignPlan = $userTarget->assignPlan($plan_request->plan_id, $plan_request->duration);
                    $price      = $plan->price;

                    if ($assignPlan['is_success'] == true && !empty($plan)) {
                        if (!empty($userTarget->payment_subscription_id) && $userTarget->payment_subscription_id != '') {
                            try {
                                $userTarget->cancel_subscription($userTarget->id);
                            } catch (\Exception $exception) {
                                \Log::debug($exception->getMessage());
                            }
                        }

                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                        Order::create([
                            'order_id' => $orderID,
                            'name' => null,
                            'email' => null,
                            'card_number' => null,
                            'card_exp_month' => null,
                            'card_exp_year' => null,
                            'plan_name' => $plan->name,
                            'plan_id' => $plan->id,
                            'price' => $price,
                            'price_currency' => !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'usd',
                            'txn_id' => '',
                            'payment_type' => __('Manually Upgrade By Super Admin'),
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $userTarget->id,
                        ]);

                        $plan_request->delete();

                        return redirect()->back()->with('success', __('Plan successfully upgraded.'));
                    } else {
                        return redirect()->back()->with('error', __('Plan fail to upgrade.'));
                    }
                } else {
                    $userTarget['requested_plan'] = 0;
                    $userTarget->update();

                    $plan_request->delete();

                    return redirect()->back()->with('success', __('Request Rejected Successfully.'));
                }
            } else {
                return redirect()->back()->with('error', __('Something went wrong.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /*
     * @id = User ID
    */
    public function cancelRequest($id)
    {
        $user = Auth::user();
        if ($user->type == 'super admin' || $user->can('Delete Plan Request') || $user->id == $id) {
            $targetUser = User::find($id);

            if ($targetUser) {
                $targetUser['requested_plan'] = '0';
                $targetUser->update();
            }

            PlanRequest::where('user_id', $id)->delete();

            return redirect()->back()->with('success', __('Request Canceled Successfully.'));
        }

        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function show(PlanRequest $planRequest)
    {
    }
}
