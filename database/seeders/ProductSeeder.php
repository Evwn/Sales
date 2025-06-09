<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $businesses = Business::all();
        $inventoryItems = InventoryItem::all();

        foreach ($businesses as $business) {
            // Electronics store products
            if (str_contains(strtolower($business->name), 'electronics')) {
                foreach ($inventoryItems as $item) {
                    if (in_array($item->brand, ['Apple', 'Samsung'])) {
                        Product::create([
                            'business_id' => $business->id,
                            'inventory_item_id' => $item->id,
                            'price' => $item->brand === 'Apple' ? 999.99 : 899.99,
                            'stock' => rand(5, 20),
                            'status' => 'active',
                        ]);
                    }
                }
            }
            // Fashion store products
            elseif (str_contains(strtolower($business->name), 'fashion')) {
                foreach ($inventoryItems as $item) {
                    if (!in_array($item->brand, ['Apple', 'Samsung'])) {
                        Product::create([
                            'business_id' => $business->id,
                            'inventory_item_id' => $item->id,
                            'price' => $item->brand === 'Levi\'s' ? 79.99 : 19.99,
                            'stock' => rand(20, 50),
                            'status' => 'active',
                        ]);
                    }
                }
            }
        }
    }
} 