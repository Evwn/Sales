<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = [
            'owner' => 'Business Owner',
            'admin' => 'Business Admin',
            'seller' => 'Sales Person',
            'supplier' => 'Supplier',
            'customer' => 'Customer',
        ];

        foreach ($roles as $name => $description) {
            if (!Role::where('name', $name)->exists()) {
                Role::create([
                    'name' => $name,
                    'guard_name' => 'web'
                ]);
            }
        }

        // Create permissions
        $permissions = [
            // Dashboard
            'view_dashboard',

            'manage_users',
            'view_users',
            'manage_roles',
            'manage_permissions',
            
            
            // Business Management
            'manage_business',
            'manage_branches',
            'manage_sellers',
            'manage_settings',
            
            // Product & Inventory
            'manage_products',
            'view_products',
            'manage_inventory',
            'view_inventory',
            
            // Sales & Purchases
            'manage_sales',
            'view_sales',
            'manage_purchases',
            'view_purchases',
            
            // Reports
            'view_reports',
            'generate_reports',
            
            // Customer & Supplier
            'manage_customers',
            'view_customers',
            'manage_suppliers',
            'view_suppliers',
            
            // Orders & Deliveries
            'manage_orders',
            'view_orders',
            'manage_deliveries',
            'view_deliveries',

            //sellers
            'manage_sellers',
            'view_sellers',
        ];

        foreach ($permissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create([
                    'name' => $permission,
                    'guard_name' => 'web'
                ]);
            }
        }

        // Assign permissions to roles
        $ownerRole = Role::findByName('admin');
        $ownerRole->givePermissionTo($permissions);

        $adminRole = Role::findByName('owner');
        $adminRole->givePermissionTo([
            'view_dashboard',
            'manage_branches',
            'manage_sellers',
            'manage_products',
            'view_products',
            'manage_inventory',
            'view_inventory',
            'manage_sales',
            'view_sales',
            'manage_purchases',
            'view_purchases',
            'view_reports',
            'generate_reports',
            'manage_customers',
            'view_customers',
            'manage_suppliers',
            'view_suppliers',
            'manage_orders',
            'view_orders',
            'manage_deliveries',
            'view_deliveries',
            'manage_settings',
            'manage_business',
            'view_sellers',

        ]);

        $sellerRole = Role::findByName('seller');
        $sellerRole->givePermissionTo([
            'view_dashboard',
            'view_inventory',
        ]);

        $supplierRole = Role::findByName('supplier');
        $supplierRole->givePermissionTo([
            'view_dashboard',
            'view_products',
            'view_inventory',
            'view_purchases',
            'view_deliveries',
        ]);

        $customerRole = Role::findByName('customer');
        $customerRole->givePermissionTo([
            'view_dashboard',
            'view_products',
            'view_orders',
            'view_deliveries',
        ]);
    }
} 