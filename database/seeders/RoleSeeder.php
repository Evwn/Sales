<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $guards = ['pos', 'backoffice'];
        $roles = ['owner', 'administrator', 'manager', 'cashier', 'admin'];

        // Permissions
        $posPermissions = [
            'accept payments',
            'apply discounts with restricted access',
            'change taxes in a sale',
            'manage all open tickets',
            'void saved items in open tickets',
            'open cash drawer without making a sale',
            'view all receipts',
            'perform refunds',
            'reprint and resend receipts',
            'view shift report',
            'manage items',
            'view cost of items',
            'change settings',
            'access to live chat support',
        ];
        $backOfficePermissions = [
            'view sales reports',
            'cancel receipts',
            'manage items',
            'view cost of items',
            'manage employees',
            'manage customers',
            'manage feature settings',
            'manage billing',
            'manage payment types',
            'manage loyalty program',
            'manage taxes',
            'manage kitchen printers',
            'manage dining options',
            'manage POS devices',
            'sign into POS using email and password',
            'access to live chat support',
        ];

        foreach ($guards as $guard) {
            // Create roles for each guard
            foreach ($roles as $role) {
                if ($role === 'cashier' && $guard !== 'pos') continue; // Only create cashier for POS
                Role::firstOrCreate([
                    'name' => $role,
                    'guard_name' => $guard
                ]);
            }
            // Create permissions for each guard
            $perms = $guard === 'pos' ? $posPermissions : $backOfficePermissions;
            foreach ($perms as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard
                ]);
            }
        }

        // Assign permissions to roles for each guard
        foreach ($guards as $guard) {
            $ownerRole = Role::where('name', 'owner')->where('guard_name', $guard)->first();
            $adminRole = Role::where('name', 'admin')->where('guard_name', $guard)->first();
            $administratorRole = Role::where('name', 'administrator')->where('guard_name', $guard)->first();
            $managerRole = Role::where('name', 'manager')->where('guard_name', $guard)->first();
            $cashierRole = Role::where('name', 'cashier')->where('guard_name', $guard)->first();
            $perms = Permission::where('guard_name', $guard)->pluck('name')->toArray();

            // Owner/Admin: all permissions for the guard
            $ownerRole?->syncPermissions($perms);
            $adminRole?->syncPermissions($perms);
            // Administrator/Manager: all for the guard
            $administratorRole?->syncPermissions($perms);
            $managerRole?->syncPermissions($perms);
            // Cashier: only POS permissions in 'pos', none in 'backoffice'
            if ($guard === 'pos') {
                $cashierRole?->syncPermissions($perms);
            } else {
                $cashierRole?->syncPermissions([]);
            }
        }
    }
} 