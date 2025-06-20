<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReceipt;
use App\Models\SalesReceiptItem;
use App\Models\SalesCommission;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function processSale(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Defensive: Get customer
            $customer = null;
            if (isset($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
            } else {
                $customer = Customer::where('name', 'Walk-in Customer')->first();
            }
            if (!$customer) {
                throw new \Exception('Customer not found.');
            }
            $customerId = $customer->id;

            // 1. Create the sale record
            $sale = Sale::create([
                'reference' => 'SALE-' . Str::random(8),
                'customer_id' => $customerId,
                'seller_id' => $data['seller_id'],
                'amount' => $data['total_amount'],
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => $data['payment_method'],
                'business_id' => $data['business_id'],
                'branch_id' => $data['branch_id'],
                'sale_date' => now(),
            ]);

            // 2. Create sale items with tax calculations
            $totalTax = 0;
            foreach ($data['items'] as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];
                $unitPrice = $product['price'];
                
                // Calculate tax for this item
                $taxAmount = 0;
                if ($product['is_taxable']) {
                    $taxAmount = ($unitPrice * $quantity * $product['tax_rate']) / 100;
                    $totalTax += $taxAmount;
                }

                // Create sale item
                $sale->items()->create([
                    'product_id' => $product['id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax' => $taxAmount,
                    'total_price' => ($unitPrice * $quantity) + $taxAmount,
                ]);
            }

            // 3. Create sales receipt
            $receipt = SalesReceipt::create([
                'reference' => 'RECEIPT-' . Str::random(8),
                'sale_id' => $sale->id,
                'business_id' => $data['business_id'],
                'branch_id' => $data['branch_id'],
                'customer_id' => $customerId,
                'cashier_id' => $data['seller_id'],
                'subtotal' => $data['total_amount'] - $totalTax,
                'tax' => $totalTax,
                'total' => $data['total_amount'],
                'total_quantity' => collect($data['items'])->sum('quantity'),
                'payment_methods' => [
                    $data['payment_method'] => $data['total_amount']
                ],
                'payment_status' => 'paid',
            ]);

            // Create receipt items
            foreach ($data['items'] as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];
                $unitPrice = $product['price'];
                $subtotal = $unitPrice * $quantity;
                $taxAmount = 0;
                
                if ($product['is_taxable']) {
                    $taxAmount = ($subtotal * $product['tax_rate']) / 100;
                }

                $receipt->items()->create([
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'product_barcode' => $product['barcode'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'tax' => $taxAmount,
                    'total' => $subtotal + $taxAmount,
                ]);
            }

            // 4. Create sales commission if applicable
            $seller = $sale->seller;
            if ($seller && $seller->hasRole('seller')) {
                $commissionRate = 0.05; // 5% commission rate
                $commissionAmount = $data['total_amount'] * $commissionRate;
                
                SalesCommission::create([
                    'seller_id' => $sale->seller_id,
                    'sale_id' => $sale->id,
                    'amount' => $commissionAmount,
                    'status' => 'pending'
                ]);
            }

            return [
                'sale' => $sale->load(['items.product', 'seller', 'business', 'branch']),
                'receipt' => $receipt
            ];
        });
    }
} 