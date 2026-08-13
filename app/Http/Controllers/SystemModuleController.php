<?php

namespace App\Http\Controllers;

use App\Models\SystemModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SystemModuleController extends Controller
{
    /**
     * Display a listing of system modules.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || $user->isSuperAdminSideUser()) {
            $query = SystemModule::with('parent');

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $modules = $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

            return view('system_module.index', compact('modules'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show the form for creating a new module.
     */
    public function create()
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || $user->isSuperAdminSideUser()) {
            $parentModules = SystemModule::whereNull('parent_id')->pluck('name', 'id')->toArray();
            return view('system_module.create', compact('parentModules'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Store a newly created module in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || $user->isSuperAdminSideUser()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'module_type' => 'required|in:super_admin,company,both',
                'available_for' => 'required|in:both,individual,business',
                'sort_order' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($request->name);

            // Ensure unique slug
            $originalSlug = $slug;
            $count = 1;
            while (SystemModule::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            SystemModule::create([
                'name' => $request->name,
                'slug' => $slug,
                'icon' => $request->icon,
                'description' => $request->description,
                'module_type' => $request->module_type,
                'available_for' => $request->available_for,
                'parent_id' => !empty($request->parent_id) ? $request->parent_id : null,
                'sort_order' => $request->sort_order ?? 0,
                'status' => $request->has('status') ? 1 : 0,
                'is_always_included' => $request->has('is_always_included') ? 1 : 0,
                'is_auto_included' => $request->has('is_auto_included') ? 1 : 0,
            ]);

            return redirect()->route('system-modules.index')->with('success', __('System Module successfully created.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show the form for editing the specified module.
     */
    public function edit(SystemModule $systemModule)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || $user->isSuperAdminSideUser()) {
            $parentModules = SystemModule::whereNull('parent_id')
                ->where('id', '!=', $systemModule->id)
                ->pluck('name', 'id')
                ->toArray();

            return view('system_module.edit', compact('systemModule', 'parentModules'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Update the specified module in storage.
     */
    public function update(Request $request, SystemModule $systemModule)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || $user->isSuperAdminSideUser()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'module_type' => 'required|in:super_admin,company,both',
                'available_for' => 'required|in:both,individual,business',
                'sort_order' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($request->name);

            // Ensure unique slug excluding current
            $originalSlug = $slug;
            $count = 1;
            while (SystemModule::where('slug', $slug)->where('id', '!=', $systemModule->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $systemModule->update([
                'name' => $request->name,
                'slug' => $slug,
                'icon' => $request->icon,
                'description' => $request->description,
                'module_type' => $request->module_type,
                'available_for' => $request->available_for,
                'parent_id' => !empty($request->parent_id) ? $request->parent_id : null,
                'sort_order' => $request->sort_order ?? 0,
                'status' => $request->has('status') ? 1 : 0,
                'is_always_included' => $request->has('is_always_included') ? 1 : 0,
                'is_auto_included' => $request->has('is_auto_included') ? 1 : 0,
            ]);

            return redirect()->route('system-modules.index')->with('success', __('System Module successfully updated.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Remove the specified module from storage.
     */
    public function destroy(SystemModule $systemModule)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || $user->isSuperAdminSideUser()) {
            $systemModule->delete();
            return redirect()->route('system-modules.index')->with('success', __('System Module successfully deleted.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Toggle module status via AJAX.
     */
    public function statusToggle(SystemModule $systemModule)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || $user->isSuperAdminSideUser()) {
            $systemModule->status = !$systemModule->status;
            $systemModule->save();

            return response()->json([
                'success' => true,
                'message' => __('System Module status updated.'),
                'status' => $systemModule->status,
            ]);
        }

        return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
    }
}
