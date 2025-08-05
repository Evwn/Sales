<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_reference',
        'order_tracking_id',
        'phone_number',
        'amount',
        'currency',
        'description',
        'payment_status',
        'payment_method',
        'callback_data',
        'pesapal_response'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'callback_data' => 'array',
        'pesapal_response' => 'array'
    ];

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    /**
     * Update payment status from PesaPal response
     */
    public function updateFromPesaPalResponse($response)
    {
        $this->update([
            'payment_status' => $response['payment_status_description'] ?? $this->payment_status,
            'payment_method' => $response['payment_method'] ?? $this->payment_method,
            'pesapal_response' => $response
        ]);
    }
} 