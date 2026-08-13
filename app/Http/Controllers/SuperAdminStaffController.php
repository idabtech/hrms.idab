<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class SuperAdminStaffController extends Controller
{
    /**
     * Display listing of Super Admin Staff users.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->isSuperAdminSideUser()) {
            $superAdmin = User::where('type', 'super admin')->first();
            $superAdminId = $superAdmin ? $superAdmin->id : 1;

            $users = User::where('created_by', $superAdminId)
                ->where('type', '!=', 'company')
                ->where('type', '!=', 'super admin')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('superadmin_staff.index', compact('users'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show create modal form for Super Admin Staff.
     */
    public function create()
    {
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->isSuperAdminSideUser()) {
            $superAdmin = User::where('type', 'super admin')->first();
            $superAdminId = $superAdmin ? $superAdmin->id : 1;

            $roles = Role::where('created_by', $superAdminId)
                ->where('name', '!=', 'company')
                ->where('name', '!=', 'employee')
                ->pluck('name', 'id');

            return view('superadmin_staff.create', compact('roles'));
        }

        return response()->json(['error' => __('Permission denied.')], 401);
    }

    /**
     * Store a newly created Super Admin Staff user.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->isSuperAdminSideUser()) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255|unique:users,email',
                    'password' => 'required|string|min:6',
                    'role' => 'required|exists:roles,id',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $superAdmin = User::where('type', 'super admin')->first();
            $superAdminId = $superAdmin ? $superAdmin->id : 1;

            $role = Role::findOrFail($request->role);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => $role->name,
                'lang' => 'en',
                'created_by' => $superAdminId,
                'is_active' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);

            $user->assignRole($role);

            return redirect()->route('superadmin-staff.index')->with('success', __('Super Admin Staff user created successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show edit modal for Super Admin Staff.
     */
    public function edit($id)
    {
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->isSuperAdminSideUser()) {
            $superAdmin = User::where('type', 'super admin')->first();
            $superAdminId = $superAdmin ? $superAdmin->id : 1;

            $user = User::where('created_by', $superAdminId)->findOrFail($id);

            $roles = Role::where('created_by', $superAdminId)
                ->where('name', '!=', 'company')
                ->where('name', '!=', 'employee')
                ->pluck('name', 'id');

            return view('superadmin_staff.edit', compact('user', 'roles'));
        }

        return response()->json(['error' => __('Permission denied.')], 401);
    }

    /**
     * Update Super Admin Staff details.
     */
    public function update(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->isSuperAdminSideUser()) {
            $superAdmin = User::where('type', 'super admin')->first();
            $superAdminId = $superAdmin ? $superAdmin->id : 1;

            $user = User::where('created_by', $superAdminId)->findOrFail($id);

            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                    'role' => 'required|exists:roles,id',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $role = Role::findOrFail($request->role);

            $user->name = $request->name;
            $user->email = $request->email;
            $user->type = $role->name;
            $user->save();

            $user->roles()->detach();
            $user->assignRole($role);

            return redirect()->route('superadmin-staff.index')->with('success', __('Super Admin Staff user updated successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Remove Super Admin Staff user.
     */
    public function destroy($id)
    {
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->isSuperAdminSideUser()) {
            $superAdmin = User::where('type', 'super admin')->first();
            $superAdminId = $superAdmin ? $superAdmin->id : 1;

            $user = User::where('created_by', $superAdminId)->findOrFail($id);
            $user->delete();

            return redirect()->route('superadmin-staff.index')->with('success', __('Super Admin Staff user deleted successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show reset password modal.
     */
    public function resetPasswordModal($id)
    {
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->isSuperAdminSideUser()) {
            $user = User::findOrFail($id);
            return view('superadmin_staff.reset_password', compact('user'));
        }

        return response()->json(['error' => __('Permission denied.')], 401);
    }

    /**
     * Update password for Super Admin Staff user.
     */
    public function resetPassword(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->isSuperAdminSideUser()) {
            $validator = Validator::make(
                $request->all(),
                [
                    'password' => 'required|string|min:6|confirmed',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $user = User::findOrFail($id);
            $user->password = Hash::make($request->password);
            $user->save();

            return redirect()->route('superadmin-staff.index')->with('success', __('Password reset successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }
}
