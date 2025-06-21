<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryItemsTableSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $items = [
            [
                'name' => 'Bread',
                'description' => '1kg white bread',
                'brand' => 'Broadways',
                'model' => null,
                'sku' => 'BRD001',
                'barcode' => '6164000000012',
                'upc' => null,
                'ean' => null,
                'isbn' => null,
                'mpn' => null,
                'unit' => 'pcs',
                'unit_value' => 1,
                'created_by' => 1,
                'last_updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'tax_rate' => 0,
                'is_taxable' => 1,
                'image_url' => null,
            ],
            [
                'name' => 'Milk',
                'description' => '500ml fresh milk',
                'brand' => 'Brookside',
                'model' => null,
                'sku' => 'MLK001',
                'barcode' => '6164000000029',
                'upc' => null,
                'ean' => null,
                'isbn' => null,
                'mpn' => null,
                'unit' => 'ltr',
                'unit_value' => 0.5,
                'created_by' => 1,
                'last_updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'tax_rate' => 0,
                'is_taxable' => 1,
                'image_url' => null,
            ],
            [
                'name' => 'Sugar',
                'description' => '2kg sugar',
                'brand' => 'Mumias',
                'model' => null,
                'sku' => 'SGR001',
                'barcode' => '6164000000036',
                'upc' => null,
                'ean' => null,
                'isbn' => null,
                'mpn' => null,
                'unit' => 'kg',
                'unit_value' => 2,
                'created_by' => 1,
                'last_updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'tax_rate' => 0,
                'is_taxable' => 1,
                'image_url' => null,
            ],
            [
                'name' => 'Maize Flour',
                'description' => '2kg maize flour',
                'brand' => 'Jogoo',
                'model' => null,
                'sku' => 'MZF001',
                'barcode' => '6164000000043',
                'upc' => null,
                'ean' => null,
                'isbn' => null,
                'mpn' => null,
                'unit' => 'kg',
                'unit_value' => 2,
                'created_by' => 1,
                'last_updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'tax_rate' => 0,
                'is_taxable' => 1,
                'image_url' => null,
            ],
            [
                'name' => 'Wheat Flour',
                'description' => '2kg wheat flour',
                'brand' => 'EXE',
                'model' => null,
                'sku' => 'WHF001',
                'barcode' => '6164000000050',
                'upc' => null,
                'ean' => null,
                'isbn' => null,
                'mpn' => null,
                'unit' => 'kg',
                'unit_value' => 2,
                'created_by' => 1,
                'last_updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'tax_rate' => 0,
                'is_taxable' => 1,
                'image_url' => null,
            ],
            // ... 95+ more items below, see note
        ];

        // Add more items to reach 100+ (auto-generate for demo)
        $brands = ['Brookside', 'KCC', 'Mumias', 'EXE', 'Jogoo', 'Ajab', 'Menengai', 'Kimbo', 'Blue Band', 'Royco', 'Kabras', 'Tuzo', 'Supaloaf', 'Festive', 'Kabras', 'Keringet', 'Dasani', 'CocaCola', 'Fanta', 'Sprite', 'Delmonte', 'SunGold', 'Fresh Fri', 'Golden Fry', 'Rina', 'Soko', 'Ndovu', 'Unga', 'Kabras', 'Kensalt', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh', 'Kensh'];
        $categories = ['Milk', 'Sugar', 'Salt', 'Rice', 'Tea Leaves', 'Coffee', 'Margarine', 'Eggs', 'Beef', 'Chicken', 'Fish', 'Sukuma Wiki', 'Tomatoes', 'Onions', 'Potatoes', 'Carrots', 'Cabbage', 'Bananas', 'Apples', 'Oranges', 'Watermelon', 'Soda', 'Juice', 'Water', 'Biscuits', 'Sweets', 'Chocolate', 'Soap', 'Detergent', 'Toothpaste', 'Tissue Paper', 'Sanitary Pads', 'Diapers', 'Matches', 'Candles', 'Batteries', 'Light Bulbs', 'Razor Blades', 'Shoe Polish', 'Pens', 'Exercise Books', 'Envelopes', 'Glue', 'Rulers', 'Erasers', 'Shaving Cream', 'Lotion', 'Shampoo', 'Toothbrush'];
        $unit_types = ['pcs', 'kg', 'ltr', 'pack', 'bottle', 'box', 'dozen'];
        $base_barcode = 6164000000104;
        for ($i = count($items); $i < 105; $i++) {
            $cat = $categories[array_rand($categories)];
            $brand = $brands[array_rand($brands)];
            $unit = $unit_types[array_rand($unit_types)];
            $sku = strtoupper(substr($cat, 0, 3)) . str_pad($i, 3, '0', STR_PAD_LEFT);
            $barcode = (string)($base_barcode + $i);
            $items[] = [
                'name' => $cat . ' ' . $brand,
                'description' => $cat . ' by ' . $brand,
                'brand' => $brand,
                'model' => null,
                'sku' => $sku,
                'barcode' => $barcode,
                'upc' => null,
                'ean' => null,
                'isbn' => null,
                'mpn' => null,
                'unit' => $unit,
                'unit_value' => rand(1, 5),
                'created_by' => 1,
                'last_updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'tax_rate' => 0,
                'is_taxable' => 1,
                'image_url' => null,
            ];
        }

        DB::table('inventory_items')->insert($items);
    }
} 