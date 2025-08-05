<?php

namespace App\Services;

use App\Models\BranchMpesaCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    protected $credentials;

    public function __construct(BranchMpesaCredential $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Get the credentials (for testing purposes)
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Get access token from M-PESA API
     */
    public function getAccessToken()
    {
        try {
            $authString = base64_encode($this->credentials->consumer_key . ':' . $this->credentials->consumer_secret);
            
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Basic ' . $authString,
                    'Content-Type' => 'application/json'
                ])
                ->get($this->credentials->getAuthUrl());

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            }

            Log::error('M-PESA Auth failed', [
                'branch_id' => $this->credentials->branch_id,
                'status' => $response->status(),
                'response' => $response->body(),
                'url' => $this->credentials->getAuthUrl(),
                'consumer_key' => substr($this->credentials->consumer_key, 0, 10) . '...' // Log partial key for debugging
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('M-PESA Auth exception', [
                'branch_id' => $this->credentials->branch_id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Initiate STK Push
     */
    public function initiateStkPush($phone, $amount, $transactionDesc = null, $paymentType = 'mpesa')
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to get access token'
            ];
        }

        try {
            // Generate password
            $timestamp = date('YmdHis');
            $password = base64_encode(
                $this->credentials->business_shortcode . 
                $this->credentials->passkey . 
                $timestamp
            );

            $payload = [
                'BusinessShortCode' => $this->credentials->business_shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int)round($amount), // Round to nearest whole number for M-PESA
                'PartyA' => $phone,
                'PartyB' => $this->credentials->business_shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => $this->credentials->getCallbackUrl($paymentType),
                'AccountReference' => $this->credentials->getAccountReference(),
                'TransactionDesc' => $transactionDesc ?? 'Payment via ' . $this->credentials->getBusinessName()
            ];

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ])
                ->post($this->credentials->getStkPushUrl(), $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['ResponseCode']) && $data['ResponseCode'] === '0') {
                    Log::info('M-PESA STK Push successful', [
                        'branch_id' => $this->credentials->branch_id,
                        'checkout_request_id' => $data['CheckoutRequestID'] ?? null,
                        'merchant_request_id' => $data['MerchantRequestID'] ?? null
                    ]);

                    return [
                        'success' => true,
                        'data' => $data,
                        'checkout_request_id' => $data['CheckoutRequestID'] ?? null,
                        'merchant_request_id' => $data['MerchantRequestID'] ?? null
                    ];
                } else {
                    Log::error('M-PESA STK Push failed', [
                        'branch_id' => $this->credentials->branch_id,
                        'response_code' => $data['ResponseCode'] ?? 'unknown',
                        'response_desc' => $data['ResponseDescription'] ?? 'unknown',
                        'error_code' => $data['errorCode'] ?? 'unknown',
                        'error_message' => $data['errorMessage'] ?? 'unknown',
                        'full_response' => $data
                    ]);

                    return [
                        'success' => false,
                        'message' => $data['ResponseDescription'] ?? 'STK Push failed',
                        'response_code' => $data['ResponseCode'] ?? null
                    ];
                }
            }

            Log::error('M-PESA STK Push HTTP error', [
                'branch_id' => $this->credentials->branch_id,
                'status' => $response->status(),
                'response' => $response->body(),
                'headers' => $response->headers(),
                'url' => $this->credentials->getStkPushUrl(),
                'payload' => $payload
            ]);

            $errorMessage = 'HTTP request failed: ' . $response->status();
            $responseBody = $response->body();
            $responseData = json_decode($responseBody, true);
            
            if ($response->status() === 403) {
                $errorMessage = 'Access denied (403). Please check your credentials and permissions.';
            } elseif ($response->status() === 401) {
                $errorMessage = 'Unauthorized (401). Please check your Consumer Key and Consumer Secret.';
            } elseif ($response->status() === 500) {
                if (isset($responseData['errorCode']) && $responseData['errorCode'] === '500.003.02') {
                    $errorMessage = 'M-PESA system is busy. Please try again in a few minutes.';
                } else {
                    $errorMessage = 'M-PESA service error (HTTP 500). Please try again later.';
                }
            }

            return [
                'success' => false,
                'message' => $errorMessage,
                'http_status' => $response->status(),
                'response_body' => $response->body(),
                'error_code' => $responseData['errorCode'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('M-PESA STK Push exception', [
                'branch_id' => $this->credentials->branch_id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if there's an ongoing transaction for a phone number
     * This helps detect error 1001 (subscriber locked) before initiating a new transaction
     */
    public function checkOngoingTransaction($phone)
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'has_ongoing_transaction' => false,
                'message' => 'Failed to get access token'
            ];
        }

        try {
            // Generate password
            $timestamp = date('YmdHis');
            $password = base64_encode(
                $this->credentials->business_shortcode . 
                $this->credentials->passkey . 
                $timestamp
            );

            $payload = [
                'BusinessShortCode' => $this->credentials->business_shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => 1, // Use minimal amount for checking
                'PartyA' => $phone,
                'PartyB' => $this->credentials->business_shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => $this->credentials->getCallbackUrl(),
                'AccountReference' => $this->credentials->getAccountReference() . '_check',
                'TransactionDesc' => 'Transaction status check'
            ];

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ])
                ->post($this->credentials->getStkPushUrl(), $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // If we get error 1001, it means there's an ongoing transaction
                if (isset($data['errorCode']) && $data['errorCode'] === '1001') {
                    return [
                        'success' => false,
                        'has_ongoing_transaction' => true,
                        'message' => 'There is an ongoing transaction for this phone number. Please wait 2-3 minutes or try a different phone number.',
                        'error_code' => '1001'
                    ];
                }
                
                // If successful, immediately cancel it to avoid actual charge
                if (isset($data['ResponseCode']) && $data['ResponseCode'] === '0') {
                    // Cancel the test transaction immediately
                    if (isset($data['CheckoutRequestID'])) {
                        $this->cancelStkPush($data['CheckoutRequestID']);
                    }
                    
                    return [
                        'success' => true,
                        'has_ongoing_transaction' => false,
                        'message' => 'No ongoing transaction detected'
                    ];
                }
            }

            return [
                'success' => false,
                'has_ongoing_transaction' => false,
                'message' => 'Unable to check transaction status'
            ];

        } catch (\Exception $e) {
            Log::error('M-PESA check ongoing transaction exception', [
                'branch_id' => $this->credentials->branch_id,
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'has_ongoing_transaction' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancel an STK Push request (if possible)
     */
    public function cancelStkPush($checkoutRequestId)
    {
        // Note: M-PESA doesn't provide a direct cancel API
        // This is more of a placeholder for future implementation
        // For now, we just log the attempt
        Log::info('M-PESA STK Push cancel attempt', [
            'branch_id' => $this->credentials->branch_id,
            'checkout_request_id' => $checkoutRequestId
        ]);

        return [
            'success' => true,
            'message' => 'Cancel request logged (M-PESA doesn\'t support direct cancellation)'
        ];
    }

    /**
     * Query STK Push status
     */
    public function queryStkPush($checkoutRequestId)
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to get access token'
            ];
        }

        try {
            // Generate password
            $timestamp = date('YmdHis');
            $password = base64_encode(
                $this->credentials->business_shortcode . 
                $this->credentials->passkey . 
                $timestamp
            );

            $payload = [
                'BusinessShortCode' => $this->credentials->business_shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId
            ];

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ])
                ->post($this->credentials->getStkPushQueryUrl(), $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('M-PESA STK Query response', [
                    'branch_id' => $this->credentials->branch_id,
                    'checkout_request_id' => $checkoutRequestId,
                    'response' => $data
                ]);

                // Check for specific error codes
                $errorCode = $data['errorCode'] ?? null;
                if ($errorCode) {
                    $errorMessage = $this->getQueryErrorMessage($errorCode);
                    return [
                        'success' => false,
                        'error_code' => $errorCode,
                        'message' => $errorMessage,
                        'data' => $data
                    ];
                }

                return [
                    'success' => true,
                    'data' => $data,
                    'result_code' => $data['ResultCode'] ?? null,
                    'result_desc' => $data['ResultDesc'] ?? null,
                    'response_code' => $data['ResponseCode'] ?? null,
                    'response_description' => $data['ResponseDescription'] ?? null,
                    'merchant_request_id' => $data['MerchantRequestID'] ?? null,
                    'checkout_request_id' => $data['CheckoutRequestID'] ?? null
                ];
            }

            Log::error('M-PESA STK Query HTTP error', [
                'branch_id' => $this->credentials->branch_id,
                'checkout_request_id' => $checkoutRequestId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'HTTP request failed: ' . $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('M-PESA STK Query exception', [
                'branch_id' => $this->credentials->branch_id,
                'checkout_request_id' => $checkoutRequestId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get error message for STK Query error codes
     */
    private function getQueryErrorMessage($errorCode)
    {
        $errorMessages = [
            '404.001.04' => 'Invalid Authentication Header - All M-PESA API requests on the Daraja platform are POST requests except Authorization API which is GET.',
            '400.002.05' => 'Invalid Request Payload - Your request body is not properly drafted. Make sure you are submitting the correct request payload.',
            '400.003.01' => 'Invalid Access Token - Might be using a wrong or expired access token. Regenerate a new token and use it before expiry.'
        ];

        return $errorMessages[$errorCode] ?? "Unknown error code: {$errorCode}";
    }

    /**
     * Poll STK Push status until final result
     */
    public function pollStkPushStatus($checkoutRequestId, $maxAttempts = 60, $delaySeconds = 1)
    {
        $attempts = 0;
        $accessToken = null;
        
        while ($attempts < $maxAttempts) {
            // Get fresh access token for each attempt to avoid token expiration issues
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to get access token during polling',
                    'result_code' => 'AUTH_FAILED',
                    'result_desc' => 'Authentication failed during polling'
                ];
            }
            
            $result = $this->queryStkPush($checkoutRequestId);
            
            if (!$result['success']) {
                // If it's an authentication error, try to get a new token and retry once
                if (str_contains($result['message'], 'Failed to get access token') && $attempts < $maxAttempts - 1) {
                    Log::warning('Access token expired during polling, retrying with new token', [
                        'branch_id' => $this->credentials->branch_id,
                        'checkout_request_id' => $checkoutRequestId,
                        'attempt' => $attempts + 1
                    ]);
                    
                    $attempts++;
                    sleep($delaySeconds);
                    continue;
                }
                return $result;
            }
            
            $resultCode = $result['result_code'];
            
            // Check if we have a final result
            if ($resultCode !== null) {
                // Final result codes: 0 = success, 1032 = cancelled, others = failed/timeout
                return $result;
            }
            
            // Still processing, wait and try again
            $attempts++;
            if ($attempts < $maxAttempts) {
                sleep($delaySeconds);
            }
        }
        
        // Timeout after max attempts
        return [
            'success' => false,
            'message' => 'Payment status polling timed out after ' . $maxAttempts . ' attempts',
            'result_code' => 'TIMEOUT',
            'result_desc' => 'Payment status polling timed out'
        ];
    }

    /**
     * Create a new instance with credentials for a specific branch
     */
    public static function forBranch($branchId, $environment = 'sandbox')
    {
        $credentials = BranchMpesaCredential::where('branch_id', $branchId)
            ->where('environment', $environment)
            ->where('is_active', true)
            ->first();

        if (!$credentials) {
            throw new \Exception("No active M-PESA credentials found for branch {$branchId}");
        }

        return new self($credentials);
    }

    /**
     * Create a new instance with credentials for a specific business
     */
    public static function forBusiness($businessId, $environment = 'sandbox')
    {
        $credentials = BranchMpesaCredential::whereHas('branch', function($query) use ($businessId) {
                $query->where('business_id', $businessId);
            })
            ->where('environment', $environment)
            ->where('is_active', true)
            ->first();

        if (!$credentials) {
            throw new \Exception("No active M-PESA credentials found for business {$businessId}");
        }

        return new self($credentials);
    }
} 