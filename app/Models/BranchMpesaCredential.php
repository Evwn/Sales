<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchMpesaCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'environment',
        'is_active',
        'is_testing',
        'consumer_key',
        'consumer_secret',
        'business_shortcode',
        'passkey',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_testing' => 'boolean',
    ];

    /**
     * Get the branch that owns these credentials
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the business through the branch
     */
    public function business()
    {
        return $this->hasOneThrough(Business::class, Branch::class, 'id', 'id', 'branch_id', 'business_id');
    }

    /**
     * Get the M-PESA API base URL based on environment
     */
    public function getApiBaseUrl()
    {
        return $this->environment === 'live' 
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Get the authentication URL
     */
    public function getAuthUrl()
    {
        return $this->getApiBaseUrl() . '/oauth/v1/generate?grant_type=client_credentials';
    }

    /**
     * Get the STK push URL
     */
    public function getStkPushUrl()
    {
        return $this->getApiBaseUrl() . '/mpesa/stkpush/v1/processrequest';
    }

    /**
     * Get the STK push query URL
     */
    public function getStkPushQueryUrl()
    {
        return $this->getApiBaseUrl() . '/mpesa/stkpushquery/v1/query';
    }

    /**
     * Get the system callback URL
     */
    public function getCallbackUrl($paymentType = 'mpesa')
    {
        // Try to get callback URL from the new callback URLs table
        $callbackUrl = PaymentCallbackUrl::getCallbackUrl(
            $paymentType,
            'safaricom',
            $this->environment,
            $this->branch_id,
            $this->branch->business_id ?? null
        );

        if ($callbackUrl) {
            return $callbackUrl->callback_url;
        }

        // Fallback to default callback URL using APP_URL
        $baseUrl = config('app.url');
        if ($paymentType === 'mpesa_pos') {
            $businessId = $this->branch->business_id ?? null;
            $branchId = $this->branch_id;
            return $baseUrl . "/api/mpesa/pos/mpesa/callback/business-{$businessId}-branch-{$branchId}";
        }
        return $baseUrl . '/api/mpesa/callback';
    }

    /**
     * Check if credentials are for testing environment
     */
    public function isTesting()
    {
        return $this->environment === 'sandbox' || $this->is_testing;
    }

    /**
     * Get account reference for transactions (using branch name)
     */
    public function getAccountReference()
    {
        return $this->branch->name ?? 'Branch Payment';
    }

    /**
     * Get business name for transaction description
     */
    public function getBusinessName()
    {
        return $this->branch->business->name ?? 'Business Payment';
    }

    /**
     * Create or update callback URL for this credential
     */
    public function createCallbackUrl($callbackUrl = null)
    {
        $baseUrl = config('app.url');
        $defaultCallbackUrl = $callbackUrl ?? $baseUrl . '/api/mpesa/callback';
        
        return PaymentCallbackUrl::createOrUpdate([
            'branch_id' => $this->branch_id,
            'business_id' => $this->branch->business_id ?? null,
            'payment_type' => 'mpesa',
            'provider' => 'safaricom',
            'environment' => $this->environment,
            'callback_url' => $defaultCallbackUrl,
            'description' => "M-PESA callback for {$this->branch->name} ({$this->environment})",
            'is_active' => $this->is_active,
            'is_verified' => true, // Mark as verified since it's created by the system
        ]);
    }

    /**
     * Get the associated callback URL record
     */
    public function callbackUrl()
    {
        return $this->hasOne(PaymentCallbackUrl::class, 'branch_id', 'branch_id')
            ->where('payment_type', 'mpesa')
            ->where('provider', 'safaricom')
            ->where('environment', $this->environment);
    }
} 