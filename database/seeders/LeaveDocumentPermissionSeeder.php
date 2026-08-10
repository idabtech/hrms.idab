<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LeaveDocumentPermissionSeeder extends Seeder
{
    /**
     * Seed Leave Document Request permissions and assign to company & hr roles.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'Request Leave Document',
            'Manage Leave Document',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $roles = Role::whereIn('name', ['company', 'hr', 'super admin'])->get();

        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }
    }
}
