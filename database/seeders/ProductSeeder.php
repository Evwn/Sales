<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $inventoryItems = InventoryItem::all();

        foreach ($branches as $branch) {
            // Electronics store products
            if (str_contains(strtolower($branch->name), 'electronics')) {
                foreach ($inventoryItems as $item) {
                    if (in_array($item->brand, ['Apple', 'Samsung'])) {
                        Product::create([
                            'inventory_item_id' => $item->id,
                            'price' => $item->brand === 'Apple' ? 999.99 : 899.99,
                            'stock' => rand(5, 20),
                            'status' => 'active',
                            'branch_id' => $branch->id
                        ]);
                    }
                }
            }
            // Fashion store products
            elseif (str_contains(strtolower($branch->name), 'fashion')) {
                foreach ($inventoryItems as $item) {
                    if (!in_array($item->brand, ['Apple', 'Samsung'])) {
                        Product::create([
                            'inventory_item_id' => $item->id,
                            'price' => $item->brand === 'Levi\'s' ? 79.99 : 19.99,
                            'stock' => rand(20, 50),
                            'status' => 'active',
                            'branch_id' => $branch->id
                        ]);
                    }
                }
            }
        }
    }
} 