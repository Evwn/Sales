<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReceipt;
use App\Models\SalesReceiptItem;
use App\Models\SalesCommission;
use App\Models\Customer;
use App\Services\LowStockNotificationService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function processSale(array $data)
    {
        \Log::info('SaleService: Starting sale processing', [
            'data_keys' => array_keys($data),
            'items_count' => count($data['items'] ?? []),
            'customer_id' => $data['customer_id'] ?? null,
            'business_id' => $data['business_id'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
        ]);

        return DB::transaction(function () use ($data) {
            \Log::info('SaleService: Starting database transaction');
            
            // Defensive: Get customer
            $customer = null;
            if (isset($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
                \Log::info('SaleService: Looking for customer by ID', ['customer_id' => $data['customer_id'], 'found' => $customer ? true : false]);
            } else {
                $customer = Customer::where('name', 'Walk-in Customer')->first();
                \Log::info('SaleService: Looking for Walk-in Customer', ['found' => $customer ? true : false]);
            }
            if (!$customer) {
                throw new \Exception('Customer not found.');
            }
            $customerId = $customer->id;
            \Log::info('SaleService: Customer found', ['customer_id' => $customerId, 'customer_name' => $customer->name]);

            // 1. Create the sale record
            \Log::info('SaleService: Creating sale record');
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
            \Log::info('SaleService: Sale created', ['sale_id' => $sale->id, 'reference' => $sale->reference]);

            // 2. Create sale items with tax calculations
            \Log::info('SaleService: Creating sale items');
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
                $saleItem = $sale->items()->create([
                    'product_id' => $product['id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax' => $taxAmount,
                ]);
                \Log::info('SaleService: Sale item created', ['item_id' => $saleItem->id, 'product_id' => $product['id']]);

                // Update product stock
                $productModel = \App\Models\Product::find($product['id']);
                if ($productModel) {
                    $currentStock = $productModel->stock ?? 0;
                    $newStock = max(0, $currentStock - $quantity);
                    $productModel->update([
                        'stock' => $newStock
                    ]);
                    \Log::info('SaleService: Product stock updated', [
                        'product_id' => $product['id'],
                        'product_name' => $product['name'],
                        'old_stock' => $currentStock,
                        'quantity_sold' => $quantity,
                        'new_stock' => $newStock
                    ]);

                    // Check if product is now low on stock and send notification
                    $lowStockService = new LowStockNotificationService();
                    if ($lowStockService->isProductLowStock($productModel)) {
                        \Log::info('SaleService: Product is now low on stock, checking for notification', [
                            'product_id' => $product['id'],
                            'product_name' => $product['name'],
                            'current_stock' => $newStock,
                            'min_stock_level' => $productModel->min_stock_level
                        ]);
                        
                        // Get the business for this product
                        $business = $productModel->business;
                        if ($business) {
                            $lowStockService->checkBusinessLowStock($business);
                        }
                    }
                } else {
                    \Log::error('SaleService: Product not found for stock update', ['product_id' => $product['id']]);
                }
            }

            // 3. Create sales receipt
            \Log::info('SaleService: Creating sales receipt');
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
            \Log::info('SaleService: Receipt created', ['receipt_id' => $receipt->id, 'reference' => $receipt->reference]);

            // Create receipt items
            \Log::info('SaleService: Creating receipt items');
            foreach ($data['items'] as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];
                $unitPrice = $product['price'];
                $subtotal = $unitPrice * $quantity;
                $taxAmount = 0;
                
                if ($product['is_taxable']) {
                    $taxAmount = ($subtotal * $product['tax_rate']) / 100;
                }

                $receiptItem = $receipt->items()->create([
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'product_barcode' => $product['barcode'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'tax' => $taxAmount,
                    'total' => $subtotal + $taxAmount,
                ]);
                \Log::info('SaleService: Receipt item created', ['item_id' => $receiptItem->id, 'product_name' => $product['name']]);
            }

            // 4. Create sales commission if applicable
            \Log::info('SaleService: Checking for commission');
            $seller = $sale->seller;
            if ($seller && $seller->hasRole('seller')) {
                $commissionRate = 0.05; // 5% commission rate
                $commissionAmount = $data['total_amount'] * $commissionRate;
                
                $commission = SalesCommission::create([
                    'seller_id' => $sale->seller_id,
                    'sale_id' => $sale->id,
                    'amount' => $commissionAmount,
                    'status' => 'pending'
                ]);
                \Log::info('SaleService: Commission created', ['commission_id' => $commission->id]);
            }

            \Log::info('SaleService: Sale processing completed successfully');
            
            // Load relationships for the sale before returning
            $sale->load(['seller', 'branch', 'items']);
            
            return [
                'sale' => $sale,
                'receipt' => $receipt
            ];
        });
    }
} 