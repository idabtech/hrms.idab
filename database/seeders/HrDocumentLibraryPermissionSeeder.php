<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class HrDocumentLibraryPermissionSeeder extends Seeder
{
    /**
     * Seed the HR Document Library permissions.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = [
            'Manage HR Document Library',
            'Create HR Document Library',
            'Edit HR Document Library',
            'Delete HR Document Library',
            'View HR Document Library',
            'Download HR Document Library',
        ];

        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // Super Admin gets all permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        if ($superAdminRole) {
            foreach ($allPermissions as $p) {
                if (!$superAdminRole->hasPermissionTo($p)) {
                    $superAdminRole->givePermissionTo($p);
                }
            }
        }

        // Company gets Manage, View, Download permissions only
        $companyPermissions = [
            'Manage HR Document Library',
            'View HR Document Library',
            'Download HR Document Library',
        ];

        $companyRole = Role::firstOrCreate(['name' => 'company', 'guard_name' => 'web']);
        if ($companyRole) {
            foreach ($companyPermissions as $p) {
                if (!$companyRole->hasPermissionTo($p)) {
                    $companyRole->givePermissionTo($p);
                }
            }
        }

        $hrRole = Role::where('name', 'hr')->first();
        if ($hrRole) {
            foreach ($companyPermissions as $p) {
                if (!$hrRole->hasPermissionTo($p)) {
                    $hrRole->givePermissionTo($p);
                }
            }
        }
    }
}
