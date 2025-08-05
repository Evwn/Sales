<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PesaPalService
{
    protected $environment;
    protected $consumerKey;
    protected $consumerSecret;
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->environment = config('pesapal.environment', 'sandbox');
        $this->consumerKey = config('pesapal.consumer_key');
        $this->consumerSecret = config('pesapal.consumer_secret');
        
        if ($this->environment === 'sandbox') {
            $this->baseUrl = 'https://cybqa.pesapal.com/pesapalv3/api';
        } else {
            $this->baseUrl = 'https://pay.pesapal.com/v3/api';
        }
    }

    public function getAccessToken()
    {
        try {
            $response = Http::withoutVerifying()->post($this->baseUrl . '/Auth/RequestToken', [
                'consumer_key' => $this->consumerKey,
                'consumer_secret' => $this->consumerSecret
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->token = $data['token'];
                return $this->token;
            }

            Log::error('PesaPal: Failed to get access token', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PesaPal: Exception getting access token', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function registerIPN($callbackUrl)
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/URLSetup/RegisterIPN', [
                'url' => $callbackUrl,
                'ipn_notification_type' => 'POST'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['ipn_id'] ?? null;
            }

            Log::error('PesaPal: Failed to register IPN', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PesaPal: Exception registering IPN', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function initiateSTKPush($phone, $amount, $description = 'Payment', $callbackUrl = null)
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        // For now, skip IPN registration to avoid issues
        $ipnId = null;

        $merchantReference = 'ORDER_' . time() . '_' . rand(1000, 9999);

        try {
            $payload = [
                'id' => $merchantReference,
                'currency' => 'KES',
                'amount' => $amount,
                'description' => $description,
                'callback_url' => $callbackUrl ?? route('pesapal.callback'),
                'branch' => config('pesapal.branch', 'DEFAULT BRANCH'),
                'billing_address' => [
                    'email_address' => config('pesapal.email', 'test@example.com'),
                    'phone_number' => $phone,
                    'country_code' => 'KE',
                    'first_name' => config('pesapal.first_name', 'Test'),
                    'middle_name' => '',
                    'last_name' => config('pesapal.last_name', 'User'),
                    'line_1' => config('pesapal.address', 'Test Address'),
                    'line_2' => '',
                    'city' => config('pesapal.city', 'Nairobi'),
                    'state' => '',
                    'postal_code' => '',
                    'zip_code' => ''
                ]
            ];

            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/Transactions/SubmitOrderRequest', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Log the payment request
                Log::info('PesaPal: STK Push initiated', [
                    'merchant_reference' => $merchantReference,
                    'amount' => $amount,
                    'phone' => $phone,
                    'tracking_id' => $data['order_tracking_id'] ?? null
                ]);

                return $data;
            }

            Log::error('PesaPal: Failed to initiate STK push', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PesaPal: Exception initiating STK push', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function checkPaymentStatus($trackingId)
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/Transactions/GetTransactionStatus', [
                'orderTrackingId' => $trackingId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('PesaPal: Payment status checked', [
                    'tracking_id' => $trackingId,
                    'status' => $data['payment_status_description'] ?? 'unknown'
                ]);

                return $data;
            }

            Log::error('PesaPal: Failed to check payment status', [
                'tracking_id' => $trackingId,
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PesaPal: Exception checking payment status', [
                'tracking_id' => $trackingId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function handleCallback($request)
    {
        $data = $request->all();
        
        Log::info('PesaPal: Callback received', $data);
        
        // Save callback data to storage
        $logFile = 'pesapal/callbacks/' . date('Y-m-d') . '.json';
        $existingData = [];
        
        if (Storage::exists($logFile)) {
            $existingData = json_decode(Storage::get($logFile), true) ?: [];
        }
        
        $existingData[] = [
            'timestamp' => now()->toISOString(),
            'data' => $data
        ];
        
        Storage::put($logFile, json_encode($existingData, JSON_PRETTY_PRINT));
        
        return response('OK', 200);
    }
} 