<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantMpesaCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'business_name',
        'environment',
        'is_active',
        'is_testing',
        'consumer_key',
        'consumer_secret',
        'business_shortcode',
        'passkey',
        'callback_url',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_testing' => 'boolean',
    ];

    /**
     * Get the tenant/business that owns these credentials
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
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
     * Check if credentials are for testing environment
     */
    public function isTesting()
    {
        return $this->environment === 'sandbox' || $this->is_testing;
    }

    /**
     * Get account reference for transactions
     */
    public function getAccountReference()
    {
        return $this->business_name;
    }
} 