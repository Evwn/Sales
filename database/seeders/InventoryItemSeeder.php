<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $items = [
            [
                'name' => 'iPhone 14 Pro',
                'description' => 'Latest iPhone model with advanced features',
                'brand' => 'Apple',
                'model' => 'iPhone 14 Pro',
                'sku' => 'IP14PRO-128GB',
                'barcode' => '123456789012',
                'upc' => '012345678901',
                'unit' => 'piece',
                'unit_value' => 1,
                'created_by' => $admin->id,
                'last_updated_by' => $admin->id,
            ],
            [
                'name' => 'Samsung Galaxy S23',
                'description' => 'Latest Samsung flagship phone',
                'brand' => 'Samsung',
                'model' => 'Galaxy S23',
                'sku' => 'SAMS23-256GB',
                'barcode' => '123456789013',
                'upc' => '012345678902',
                'unit' => 'piece',
                'unit_value' => 1,
                'created_by' => $admin->id,
                'last_updated_by' => $admin->id,
            ],
            [
                'name' => 'Cotton T-Shirt',
                'description' => 'Basic cotton t-shirt',
                'brand' => 'Generic',
                'model' => 'Basic Tee',
                'sku' => 'TSH-BLK-L',
                'barcode' => '123456789014',
                'upc' => '012345678903',
                'unit' => 'piece',
                'unit_value' => 1,
                'created_by' => $admin->id,
                'last_updated_by' => $admin->id,
            ],
            [
                'name' => 'Denim Jeans',
                'description' => 'Classic denim jeans',
                'brand' => 'Levi\'s',
                'model' => '501',
                'sku' => 'LEV-501-32',
                'barcode' => '123456789015',
                'upc' => '012345678904',
                'unit' => 'piece',
                'unit_value' => 1,
                'created_by' => $admin->id,
                'last_updated_by' => $admin->id,
            ],
        ];

        foreach ($items as $item) {
            InventoryItem::updateOrCreate(
                ['sku' => $item['sku']],
                $item
            );
        }
    }
} 