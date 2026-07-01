<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SalaryRevisionPermissionSeeder extends Seeder
{
    /**
     * Seed salary revision permissions and assign them to roles.
     *
     * Permissions:
     *   Manage Salary Revision  — view the salary revision page
     *   Create Salary Revision  — create new salary revisions
     *   Edit Salary Revision    — edit existing salary revisions
     *   Delete Salary Revision  — delete salary revisions
     */
    public function run(): void
    {
        $permissionNames = ['Manage Salary Revision', 'Create Salary Revision', 'Edit Salary Revision', 'Delete Salary Revision'];

        // Create permissions if they don't exist (fires Eloquent events → clears Spatie cache)
        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Assign all salary revision permissions to admin-level roles
        $adminRoles = ['super admin', 'company', 'hr'];
        foreach ($adminRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissionNames);
            }
        }

        // Employees can only view the salary revision (Manage Salary Revision)
        $employeeRole = Role::where('name', 'employee')->first();
        if ($employeeRole) {
            $employeeRole->givePermissionTo('Manage Salary Revision');
        }
    }
}
