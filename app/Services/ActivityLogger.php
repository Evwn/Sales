<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(
        string $type,
        array $data,
        ?Model $subject = null,
        ?Model $causer = null
    ): Activity {
        return Activity::create([
            'type' => $type,
            'data' => $data,
            'user_id' => auth()->id(),
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer?->id,
        ]);
    }

    public static function logSaleCreated($sale, $user)
    {
        return self::log('sale_created', [
            'amount' => $sale->amount,
            'seller_name' => $sale->seller->name,
            'branch_name' => $sale->branch->name,
            'items_count' => $sale->items->count(),
        ], $sale, $user);
    }

    public static function logProductUpdated($product, $user)
    {
        return self::log('product_updated', [
            'product_name' => $product->name,
            'old_price' => $product->getOriginal('price'),
            'new_price' => $product->price,
            'old_stock' => $product->getOriginal('stock'),
            'new_stock' => $product->stock,
        ], $product, $user);
    }

    public static function logInventoryAdjusted($product, $user, $oldStock, $newStock)
    {
        return self::log('inventory_adjusted', [
            'product_name' => $product->name,
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'adjustment' => $newStock - $oldStock,
        ], $product, $user);
    }

    public static function logUserLoggedIn($user)
    {
        return self::log('user_logged_in', [
            'user_name' => $user->name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $user, $user);
    }

    public static function logPaymentReceived($payment, $user)
    {
        return self::log('payment_received', [
            'amount' => $payment->amount,
            'method' => $payment->method,
            'reference' => $payment->reference,
            'status' => $payment->status,
        ], $payment, $user);
    }

    public static function logBranchCreated($branch, $user)
    {
        return self::log('branch_created', [
            'branch_name' => $branch->name,
            'location' => $branch->location,
            'manager_name' => $branch->manager->name ?? 'Not assigned',
        ], $branch, $user);
    }

    public static function logBusinessUpdated($business, $user)
    {
        return self::log('business_updated', [
            'business_name' => $business->name,
            'old_status' => $business->getOriginal('status'),
            'new_status' => $business->status,
        ], $business, $user);
    }
} 