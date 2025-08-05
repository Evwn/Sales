<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class PaymentCallbackUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'business_id',
        'payment_type',
        'provider',
        'environment',
        'callback_url',
        'webhook_url',
        'success_url',
        'failure_url',
        'cancel_url',
        'headers',
        'metadata',
        'description',
        'is_active',
        'is_verified',
        'last_callback_at',
        'verified_at',
        'secret_key',
        'signature_header',
    ];

    protected $casts = [
        'headers' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'last_callback_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the branch that owns the callback URL
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the business that owns the callback URL
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Scope for active callbacks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for verified callbacks
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
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
     * Get callback URL for specific criteria
     */
    public static function getCallbackUrl($paymentType, $provider, $environment, $branchId = null, $businessId = null)
    {
        $query = self::active()->verified()
            ->paymentType($paymentType)
            ->provider($provider)
            ->environment($environment);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        } elseif ($businessId) {
            $query->where('business_id', $businessId);
        }

        return $query->first();
    }

    /**
     * Create or update callback URL
     */
    public static function createOrUpdate($data)
    {
        $conditions = [
            'payment_type' => $data['payment_type'],
            'provider' => $data['provider'],
            'environment' => $data['environment'],
        ];

        if (isset($data['branch_id'])) {
            $conditions['branch_id'] = $data['branch_id'];
        } elseif (isset($data['business_id'])) {
            $conditions['business_id'] = $data['business_id'];
        }

        return self::updateOrCreate($conditions, $data);
    }

    /**
     * Mark callback as received
     */
    public function markCallbackReceived()
    {
        $this->update([
            'last_callback_at' => now(),
        ]);

        Log::info('Payment callback received', [
            'callback_id' => $this->id,
            'payment_type' => $this->payment_type,
            'provider' => $this->provider,
            'callback_url' => $this->callback_url,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Mark callback as verified
     */
    public function markAsVerified()
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Get formatted callback information
     */
    public function getFormattedInfo()
    {
        return [
            'id' => $this->id,
            'payment_type' => $this->payment_type,
            'provider' => $this->provider,
            'environment' => $this->environment,
            'callback_url' => $this->callback_url,
            'webhook_url' => $this->webhook_url,
            'success_url' => $this->success_url,
            'failure_url' => $this->failure_url,
            'cancel_url' => $this->cancel_url,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'last_callback_at' => $this->last_callback_at?->format('Y-m-d H:i:s'),
            'verified_at' => $this->verified_at?->format('Y-m-d H:i:s'),
            'description' => $this->description,
            'branch_name' => $this->branch?->name,
            'business_name' => $this->business?->name,
        ];
    }

    /**
     * Validate callback URL format
     */
    public function validateCallbackUrl()
    {
        return filter_var($this->callback_url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Get callback URL with parameters
     */
    public function getCallbackUrlWithParams($params = [])
    {
        $url = $this->callback_url;
        
        if (!empty($params)) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . http_build_query($params);
        }
        
        return $url;
    }

    /**
     * Check if callback URL is accessible
     */
    public function isAccessible()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($this->callback_url);
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Callback URL not accessible', [
                'callback_id' => $this->id,
                'callback_url' => $this->callback_url,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
