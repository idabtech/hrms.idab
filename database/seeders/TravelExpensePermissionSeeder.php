<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TravelExpensePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds for Travel Expenses & Vouchers module.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'Manage Travel Expense',
            'Create Travel Expense',
            'Edit Travel Expense',
            'Delete Travel Expense',
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
