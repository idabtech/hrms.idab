<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class SuperAdminPermissionSeeder extends Seeder
{
    /**
     * Safe & Idempotent Seeder for Super Admin Permissions.
     * New permissions can be added to the array below anytime.
     * Re-running this seeder will NOT overwrite or remove existing permissions.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // -------------------------------------------------------------
        // List of all Super Admin Side Permissions
        // Add any future Super Admin permissions to this array!
        // -------------------------------------------------------------
        $superAdminPermissions = [
            // User / Companies
            'Manage User',
            'Create User',
            'Edit User',
            'Delete User',
            'Reset Password User',
            'Login Enable Disable User',

            // Role / Staff Roles
            'Manage Role',
            'Create Role',
            'Edit Role',
            'Delete Role',

            // HR Document Library
            'Manage HR Document Library',
            'Create HR Document Library',
            'Edit HR Document Library',
            'Delete HR Document Library',
            'View HR Document Library',
            'Download HR Document Library',

            // Plan
            'Manage Plan',
            'Create Plan',
            'Edit Plan',
            'Delete Plan',
            'Buy Plan',

            // Plan Request
            'Manage Plan Request',
            'Approve Plan Request',
            'Delete Plan Request',

            // Storage Addons
            'Manage Storage Addons',
            'Create Storage Addons',
            'Edit Storage Addons',
            'Delete Storage Addons',

            // Referral Program
            'Manage Referral Program',
            'Edit Referral Program',
            'Approve Referral Program',
            'Delete Referral Program',

            // Coupon
            'manage coupon',
            'create coupon',
            'edit coupon',
            'delete coupon',

            // Order
            'Manage Order',
            'Delete Order',

            // Email Template
            'Manage Email Template',
            'Create Email Template',
            'Edit Email Template',

            // Landing Page
            'Manage Landing Page',

            // System Setup / Settings
            'Manage System Setup',
            'Manage System Settings',

            // Language
            'Manage Language',
            'Create Language',
            'Edit Language',
            'Delete Language',
        ];

        // 1. Ensure permissions exist in DB without overwriting existing
        foreach ($superAdminPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // 2. Assign all permissions to 'super admin' Role
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        if ($superAdminRole) {
            foreach ($superAdminPermissions as $permName) {
                $permObj = Permission::where('name', $permName)->where('guard_name', 'web')->first();
                if ($permObj && !$superAdminRole->hasPermissionTo($permObj)) {
                    $superAdminRole->givePermissionTo($permObj);
                }
            }
        }

        // 3. Assign all permissions to all Super Admin users
        $superAdminUsers = User::where('type', 'super admin')->get();
        foreach ($superAdminUsers as $superUser) {
            foreach ($superAdminPermissions as $permName) {
                $permObj = Permission::where('name', $permName)->where('guard_name', 'web')->first();
                if ($permObj && !$superUser->hasPermissionTo($permObj)) {
                    $superUser->givePermissionTo($permObj);
                }
            }
        }

        // 4. Assign all permissions to Super Admin Staff Roles
        $superAdminUser = User::where('type', 'super admin')->first();
        if ($superAdminUser) {
            $saStaffRoles = Role::where('created_by', $superAdminUser->id)->get();
            foreach ($saStaffRoles as $saRole) {
                foreach ($superAdminPermissions as $permName) {
                    $permObj = Permission::where('name', $permName)->where('guard_name', 'web')->first();
                    if ($permObj && !$saRole->hasPermissionTo($permObj)) {
                        $saRole->givePermissionTo($permObj);
                    }
                }
            }
        }
    }
}
