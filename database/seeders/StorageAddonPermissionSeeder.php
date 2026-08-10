<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StorageAddonPermissionSeeder extends Seeder
{
    /**
     * Seed storage addon permissions and assign them to super admin role.
     *
     * Permissions:
     *   Manage Storage Addon  — view storage addons page
     *   Create Storage Addon  — create new storage addon
     *   Edit Storage Addon    — edit existing storage addon
     *   Delete Storage Addon  — delete storage addon
     */
    public function run(): void
    {
        $permissionNames = [
            'Manage Storage Addon',
            'Create Storage Addon',
            'Edit Storage Addon',
            'Delete Storage Addon'
        ];

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $superAdminRole = Role::where('name', 'super admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissionNames);
        }
    }
}
