<?php

namespace App\Http\Controllers;

use App\Models\StorageAddon;
use App\Models\StorageAddonOrder;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class StorageAddonController extends Controller
{
    public function index()
    {
        if (Auth::user()->type === 'super admin' || Auth::user()->isSuperAdminSideUser() || Auth::user()->can('Manage Storage Addon') || Auth::user()->can('Manage Storage Addons')) {
            $addons = StorageAddon::orderBy('id', 'desc')->get();
            $orders = StorageAddonOrder::with(['user', 'storageAddon'])->orderBy('id', 'desc')->get();
            return view('storage_addon.index', compact('addons', 'orders'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function create()
    {
        if (Auth::user()->type === 'super admin' || Auth::user()->can('Create Storage Addon') || Auth::user()->can('Create Storage Addons')) {
            $addonTypes = StorageAddon::$addonTypes;
            $durationTypes = StorageAddon::$durationTypes;
            return view('storage_addon.create', compact('addonTypes', 'durationTypes'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->type === 'super admin' || Auth::user()->can('Create Storage Addon') || Auth::user()->can('Create Storage Addons')) {
            $validator = Validator::make($request->all(), [
                'title'          => 'required|string|max:255',
                'price'          => 'required|numeric|min:0',
                'storage_amount' => 'required|numeric|min:1',
                'addon_type'     => 'required|in:plan_expiry,fixed_duration',
                'duration_value' => 'required_if:addon_type,fixed_duration|nullable|numeric|min:1',
                'duration_type'  => 'required_if:addon_type,fixed_duration|nullable|in:year,month,day',
                'description'    => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            StorageAddon::create([
                'title'          => $request->title,
                'price'          => $request->price,
                'storage_amount' => $request->storage_amount,
                'addon_type'     => $request->addon_type,
                'duration_value' => $request->addon_type === 'fixed_duration' ? ($request->duration_value ?? 1) : null,
                'duration_type'  => $request->addon_type === 'fixed_duration' ? ($request->duration_type ?? 'year') : null,
                'description'    => $request->description,
                'is_active'      => $request->has('is_active') ? 1 : 0,
                'created_by'     => Auth::user()->id,
            ]);

            return redirect()->route('storage-addons.index')->with('success', __('Storage Addon created successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function edit($id)
    {
        if (Auth::user()->type === 'super admin' || Auth::user()->can('Edit Storage Addon') || Auth::user()->can('Edit Storage Addons')) {
            $addon = StorageAddon::findOrFail($id);
            $addonTypes = StorageAddon::$addonTypes;
            $durationTypes = StorageAddon::$durationTypes;
            return view('storage_addon.edit', compact('addon', 'addonTypes', 'durationTypes'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->type === 'super admin' || Auth::user()->can('Edit Storage Addon') || Auth::user()->can('Edit Storage Addons')) {
            $addon = StorageAddon::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title'          => 'required|string|max:255',
                'price'          => 'required|numeric|min:0',
                'storage_amount' => 'required|numeric|min:1',
                'addon_type'     => 'required|in:plan_expiry,fixed_duration',
                'duration_value' => 'required_if:addon_type,fixed_duration|nullable|numeric|min:1',
                'duration_type'  => 'required_if:addon_type,fixed_duration|nullable|in:year,month,day',
                'description'    => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $addon->update([
                'title'          => $request->title,
                'price'          => $request->price,
                'storage_amount' => $request->storage_amount,
                'addon_type'     => $request->addon_type,
                'duration_value' => $request->addon_type === 'fixed_duration' ? ($request->duration_value ?? 1) : null,
                'duration_type'  => $request->addon_type === 'fixed_duration' ? ($request->duration_type ?? 'year') : null,
                'description'    => $request->description,
                'is_active'      => $request->has('is_active') ? 1 : 0,
            ]);

            return redirect()->route('storage-addons.index')->with('success', __('Storage Addon updated successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function destroy($id)
    {
        if (Auth::user()->type === 'super admin' || Auth::user()->can('Delete Storage Addon') || Auth::user()->can('Delete Storage Addons')) {
            $addon = StorageAddon::findOrFail($id);
            $addon->delete();

            return redirect()->route('storage-addons.index')->with('success', __('Storage Addon deleted successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function statusToggle($id)
    {
        if (Auth::user()->type === 'super admin' || Auth::user()->can('Edit Storage Addon') || Auth::user()->can('Edit Storage Addons')) {
            $addon = StorageAddon::findOrFail($id);
            $addon->is_active = !$addon->is_active;
            $addon->save();

            return redirect()->back()->with('success', __('Storage Addon status updated successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show Payment Selection Modal/Page for Storage Addon
     */
    public function pay($code)
    {
        try {
            $addon_id = Crypt::decrypt($code);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Storage Addon Not Found.'));
        }

        $addon = StorageAddon::findOrFail($addon_id);
        $admin_payment_setting = Utility::getAdminPaymentSetting();

        return view('storage_addon.payment', compact('addon', 'admin_payment_setting'));
    }

    /**
     * Process Payment for Storage Addon
     */
    public function processPayment(Request $request, $code)
    {
        try {
            $addon_id = Crypt::decrypt($code);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Storage Addon Not Found.'));
        }

        $addon = StorageAddon::findOrFail($addon_id);
        $user = Auth::user();
        $admin_payment_setting = Utility::getAdminPaymentSetting();
        $orderID = 'SAO_' . strtoupper(str_replace('.', '', uniqid('', true)));

        $payment_type = $request->payment_type ?? 'Manual';
        $receipt = null;

        if ($request->hasFile('receipt')) {
            $fileName = time() . '_' . $request->file('receipt')->getClientOriginalName();
            $request->file('receipt')->storeAs('uploads/storage_addon_receipts', $fileName);
            $receipt = 'uploads/storage_addon_receipts/' . $fileName;
        }

        if ($payment_type === 'Free' || $addon->price == 0) {
            StorageAddonOrder::create([
                'order_id'         => $orderID,
                'user_id'          => $user->id,
                'storage_addon_id' => $addon->id,
                'addon_title'      => $addon->title,
                'storage_amount'   => $addon->storage_amount,
                'price'            => 0,
                'price_currency'   => $admin_payment_setting['currency'] ?? 'USD',
                'payment_type'     => 'Free',
                'payment_status'   => 'succeeded',
            ]);

            // Add storage immediately to user storage limit
            $user->storage_limit = ((float) ($user->storage_limit ?? 0)) + (float) $addon->storage_amount;
            $user->save();

            return redirect()->route('plans.index', ['tab' => 'storage_addons'])->with('success', __('Free Storage Addon claimed successfully! Storage limit updated.'));
        }

        if ($payment_type === 'Bank Transfer' || $payment_type === 'Manual') {
            // Bank Transfer / Manual Order Request - Requires Super Admin Approval
            StorageAddonOrder::create([
                'order_id'         => $orderID,
                'user_id'          => $user->id,
                'storage_addon_id' => $addon->id,
                'addon_title'      => $addon->title,
                'storage_amount'   => $addon->storage_amount,
                'price'            => $addon->price,
                'price_currency'   => $admin_payment_setting['currency'] ?? 'USD',
                'payment_type'     => $payment_type,
                'payment_status'   => 'Pending',
                'receipt'          => $receipt,
            ]);

            return redirect()->route('plans.index', ['tab' => 'storage_addons'])->with('success', __('Storage Addon order request submitted successfully. Awaiting Super Admin approval.'));
        }

        // Online / Instant Gateway Payment (Stripe, Razorpay, Paypal, Paystack, etc.)
        StorageAddonOrder::create([
            'order_id'         => $orderID,
            'user_id'          => $user->id,
            'storage_addon_id' => $addon->id,
            'addon_title'      => $addon->title,
            'storage_amount'   => $addon->storage_amount,
            'price'            => $addon->price,
            'price_currency'   => $admin_payment_setting['currency'] ?? 'USD',
            'payment_type'     => $payment_type,
            'payment_status'   => 'succeeded',
            'receipt'          => $receipt,
        ]);

        return redirect()->route('plans.index', ['tab' => 'storage_addons'])->with('success', __('Storage Addon purchased successfully! Storage limit updated.'));
    }

    /**
     * Super Admin Approval for Pending Storage Addon Request
     */
    public function approveOrder($id)
    {
        if (Auth::user()->type === 'super admin') {
            $order = StorageAddonOrder::findOrFail($id);

            if ($order->payment_status === 'Pending') {
                $order->payment_status = 'Approved';
                $order->save();

                return redirect()->route('storage-addons.index', ['tab' => 'orders'])->with('success', __('Storage Addon request approved and company storage limit updated!'));
            }

            return redirect()->route('storage-addons.index', ['tab' => 'orders'])->with('error', __('Order status is not Pending.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Super Admin Rejection for Storage Addon Request
     */
    public function rejectOrder($id)
    {
        if (Auth::user()->type === 'super admin') {
            $order = StorageAddonOrder::findOrFail($id);

            if ($order->payment_status === 'Pending') {
                $order->payment_status = 'Rejected';
                $order->save();

                return redirect()->route('storage-addons.index', ['tab' => 'orders'])->with('success', __('Storage Addon request rejected.'));
            }

            return redirect()->route('storage-addons.index', ['tab' => 'orders'])->with('error', __('Order status is not Pending.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Display Company's Storage Addon Orders in Modal Popup
     */
    public function myOrders()
    {
        if (Auth::user()->type === 'company') {
            $orders = StorageAddonOrder::where('user_id', Auth::id())->latest()->get();
            return view('storage_addon.my_orders', compact('orders'));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
