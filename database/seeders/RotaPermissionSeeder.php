<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RotaPermissionSeeder extends Seeder
{
    /**
     * Seed rota (shift scheduling) permissions and assign them to roles.
     *
     * Permissions:
     *   Manage Rota  — view the rota calendar page
     *   Create Rota  — add / assign shifts (single + bulk)
     *   Edit Rota    — edit / override existing shifts
     *   Delete Rota  — delete / suppress shifts
     */
    public function run(): void
    {
        $permissionNames = ['Manage Rota', 'Create Rota', 'Edit Rota', 'Delete Rota'];

        // Create permissions if they don't exist (fires Eloquent events → clears Spatie cache)
        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Assign all rota permissions to admin-level roles
        $adminRoles = ['super admin', 'company', 'hr'];
        foreach ($adminRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissionNames);
            }
        }

        // Employees can only view the rota (Manage Rota)
        $employeeRole = Role::where('name', 'employee')->first();
        if ($employeeRole) {
            $employeeRole->givePermissionTo('Manage Rota');
        }
    }
}
