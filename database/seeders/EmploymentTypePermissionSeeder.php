<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EmploymentTypePermissionSeeder extends Seeder
{
    /**
     * Seed the employment type permissions.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'Manage Employment Type',
                'guard_name' => 'web',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Create Employment Type',
                'guard_name' => 'web',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Edit Employment Type',
                'guard_name' => 'web',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Delete Employment Type',
                'guard_name' => 'web',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        Permission::upsert($permissions, ['name'], ['guard_name', 'updated_at']);

        $permissionNames = collect($permissions)->pluck('name')->all();
        $roleNames = ['super admin', 'company', 'hr'];

        foreach ($roleNames as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissionNames);
            }
        }
    }
}
