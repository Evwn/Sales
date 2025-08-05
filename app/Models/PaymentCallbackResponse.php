<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class PaymentCallbackResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'callback_url_id',
        'branch_id',
        'business_id',
        'payment_type',
        'provider',
        'environment',
        'merchant_request_id',
        'checkout_request_id',
        'result_code',
        'result_desc',
        'amount',
        'mpesa_receipt_number',
        'transaction_date',
        'phone_number',
        'balance',
        'callback_data',
        'headers',
        'ip_address',
        'user_agent',
        'status',
        'is_processed',
        'processed_at',
        'error_message',
        'error_details',
    ];

    protected $casts = [
        'callback_data' => 'array',
        'headers' => 'array',
        'error_details' => 'array',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the callback URL that received this response
     */
    public function callbackUrl(): BelongsTo
    {
        return $this->belongsTo(PaymentCallbackUrl::class, 'callback_url_id');
    }

    /**
     * Get the branch associated with this response
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the business associated with this response
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Scope for successful responses
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed responses
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'cancelled', 'timeout']);
    }

    /**
     * Scope for pending responses
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for specific payment type
     */
    public function scopePaymentType($query, $paymentType)
    {
        return $query->where('payment_type', $paymentType);
    }

    /**
     * Scope for specific provider
     */
    public function scopeProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope for specific environment
     */
    public function scopeEnvironment($query, $environment)
    {
        return $query->where('environment', $environment);
    }

    /**
     * Create a callback response from M-PESA data
     */
    public static function createFromMpesaCallback($callbackData, $request = null)
    {
        try {
            $stkCallback = $callbackData['Body']['stkCallback'] ?? null;
            
            if (!$stkCallback) {
                Log::warning('Invalid M-PESA callback format', ['callback_data' => $callbackData]);
                return null;
            }

            // Extract basic information
            $merchantRequestId = $stkCallback['MerchantRequestID'] ?? null;
            $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
            $resultCode = $stkCallback['ResultCode'] ?? null;
            $resultDesc = $stkCallback['ResultDesc'] ?? null;

            // Determine status based on result code
            $status = self::determineStatus($resultCode);

            // Extract transaction details if available
            $transactionDetails = self::extractTransactionDetails($stkCallback);

            // Create the response record
            $response = self::create([
                'payment_type' => 'mpesa',
                'provider' => 'safaricom',
                'environment' => 'sandbox', // This should be determined from the request context
                'merchant_request_id' => $merchantRequestId,
                'checkout_request_id' => $checkoutRequestId,
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'amount' => $transactionDetails['amount'] ?? null,
                'mpesa_receipt_number' => $transactionDetails['mpesa_receipt_number'] ?? null,
                'transaction_date' => $transactionDetails['transaction_date'] ?? null,
                'phone_number' => $transactionDetails['phone_number'] ?? null,
                'balance' => $transactionDetails['balance'] ?? null,
                'callback_data' => $callbackData,
                'headers' => $request ? $request->headers->all() : null,
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? $request->userAgent() : null,
                'status' => $status,
                'is_processed' => false,
            ]);

            Log::info('M-PESA callback response saved', [
                'response_id' => $response->id,
                'merchant_request_id' => $merchantRequestId,
                'checkout_request_id' => $checkoutRequestId,
                'result_code' => $resultCode,
                'status' => $status,
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error('Failed to create callback response', [
                'error' => $e->getMessage(),
                'callback_data' => $callbackData,
            ]);
            return null;
        }
    }

    /**
     * Determine status based on M-PESA result code
     */
    private static function determineStatus($resultCode)
    {
        if ($resultCode === '0') {
            return 'success';
        } elseif ($resultCode === '1032') {
            return 'cancelled';
        } elseif (in_array($resultCode, ['1037', '1025', '9999', '1', '2001', '1019', '1001'])) {
            return 'failed';
        } else {
            return 'pending';
        }
    }

    /**
     * Extract transaction details from M-PESA callback
     */
    private static function extractTransactionDetails($stkCallback)
    {
        $details = [];

        if (isset($stkCallback['CallbackMetadata']['Item'])) {
            foreach ($stkCallback['CallbackMetadata']['Item'] as $item) {
                $name = $item['Name'] ?? '';
                $value = $item['Value'] ?? '';

                switch ($name) {
                    case 'Amount':
                        $details['amount'] = $value;
                        break;
                    case 'MpesaReceiptNumber':
                        $details['mpesa_receipt_number'] = $value;
                        break;
                    case 'TransactionDate':
                        $details['transaction_date'] = $value;
                        break;
                    case 'PhoneNumber':
                        $details['phone_number'] = $value;
                        break;
                    case 'Balance':
                        // Handle complex balance object from M-PESA
                        if (is_string($value) && strpos($value, '{') !== false) {
                            // Extract the BasicAmount from the balance object
                            if (preg_match('/BasicAmount=([0-9.]+)/', $value, $matches)) {
                                $details['balance'] = $matches[1];
                            } else {
                                $details['balance'] = null;
                            }
                        } else {
                            $details['balance'] = $value;
                        }
                        break;
                }
            }
        }

        return $details;
    }

    /**
     * Mark response as processed
     */
    public function markAsProcessed()
    {
        $this->update([
            'is_processed' => true,
            'processed_at' => now(),
        ]);
    }

    /**
     * Get user-friendly status description
     */
    public function getStatusDescription()
    {
        $descriptions = [
            'pending' => 'Awaiting processing',
            'success' => 'Payment successful',
            'failed' => 'Payment failed',
            'cancelled' => 'Payment cancelled by user',
            'timeout' => 'Payment timed out',
        ];

        return $descriptions[$this->status] ?? 'Unknown status';
    }

    /**
     * Get formatted response information
     */
    public function getFormattedInfo()
    {
        return [
            'id' => $this->id,
            'payment_type' => $this->payment_type,
            'provider' => $this->provider,
            'environment' => $this->environment,
            'merchant_request_id' => $this->merchant_request_id,
            'checkout_request_id' => $this->checkout_request_id,
            'result_code' => $this->result_code,
            'result_desc' => $this->result_desc,
            'amount' => $this->amount,
            'mpesa_receipt_number' => $this->mpesa_receipt_number,
            'transaction_date' => $this->transaction_date,
            'phone_number' => $this->phone_number,
            'balance' => $this->balance,
            'status' => $this->status,
            'status_description' => $this->getStatusDescription(),
            'is_processed' => $this->is_processed,
            'processed_at' => $this->processed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'ip_address' => $this->ip_address,
        ];
    }
}
