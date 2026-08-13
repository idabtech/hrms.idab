<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class NewPermissionSeeder extends Seeder
{
    /**
     * Seed new HR Document Library, Super Admin permissions safely without breaking existing ones.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // All Super Admin permissions list
        $superAdminPermissions = [
            'Manage HR Document Library',
            'Create HR Document Library',
            'Edit HR Document Library',
            'Delete HR Document Library',
            'View HR Document Library',
            'Download HR Document Library',
            'Manage Plan Request',
            'Manage Storage Addons',
            'Manage Referral Program',
            'Manage Email Template',
            'Manage Landing Page',
            'Manage System Setup',
            'Manage Order',
            'manage coupon',
            'create coupon',
            'edit coupon',
            'delete coupon',
        ];

        // Ensure all permissions exist in DB
        foreach ($superAdminPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // 1. Assign all Super Admin permissions to Super Admin role
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        if ($superAdminRole) {
            foreach ($superAdminPermissions as $permName) {
                if (!$superAdminRole->hasPermissionTo($permName)) {
                    $superAdminRole->givePermissionTo($permName);
                }
            }
        }

        // Give all permissions to Super Admin users
        $superAdminUsers = User::where('type', 'super admin')->get();
        foreach ($superAdminUsers as $superUser) {
            foreach ($superAdminPermissions as $permName) {
                if (!$superUser->hasPermissionTo($permName)) {
                    $superUser->givePermissionTo($permName);
                }
            }
        }

        // 2. Assign to Company (Manage, View, Download ONLY - No Create, Edit, or Delete)
        $companyPermissions = [
            'Manage HR Document Library',
            'View HR Document Library',
            'Download HR Document Library',
        ];

        $companyRole = Role::firstOrCreate(['name' => 'company', 'guard_name' => 'web']);
        if ($companyRole) {
            foreach ($companyPermissions as $permName) {
                if (!$companyRole->hasPermissionTo($permName)) {
                    $companyRole->givePermissionTo($permName);
                }
            }
        }

        // Assign company permissions to existing company users
        $companyUsers = User::where('type', 'company')->get();
        foreach ($companyUsers as $companyUser) {
            foreach ($companyPermissions as $permName) {
                if (!$companyUser->hasPermissionTo($permName)) {
                    $companyUser->givePermissionTo($permName);
                }
            }
        }

        // 3. Assign company permissions to HR role if exists
        $hrRole = Role::where('name', 'hr')->first();
        if ($hrRole) {
            foreach ($companyPermissions as $permName) {
                if (!$hrRole->hasPermissionTo($permName)) {
                    $hrRole->givePermissionTo($permName);
                }
            }
        }
    }
}
