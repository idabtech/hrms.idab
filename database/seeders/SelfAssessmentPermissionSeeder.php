<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SelfAssessmentPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds for Self Assessment module.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'Manage Self Assessment',
            'Create Self Assessment',
            'Edit Self Assessment',
            'Delete Self Assessment',
            'Review Self Assessment',
            'Bulk Generate Self Assessment',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $roles = Role::whereIn('name', ['company', 'super admin'])->get();

        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }
    }
}
