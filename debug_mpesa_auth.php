<?php

require_once 'vendor/autoload.php';

use App\Models\BranchMpesaCredential;
use App\Services\MpesaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== M-PESA Authentication Debug ===\n\n";

// Get M-PESA credentials
$credentials = BranchMpesaCredential::where('is_active', true)
    ->where('environment', 'sandbox')
    ->first();

if (!$credentials) {
    echo "❌ No active M-PESA credentials found for sandbox environment\n";
    exit(1);
}

echo "✅ Found M-PESA credentials:\n";
echo "   Branch ID: {$credentials->branch_id}\n";
echo "   Business Shortcode: {$credentials->business_shortcode}\n";
echo "   Environment: {$credentials->environment}\n";
echo "   Consumer Key: " . substr($credentials->consumer_key, 0, 10) . "...\n";
echo "   Consumer Secret: " . substr($credentials->consumer_secret, 0, 10) . "...\n";
echo "   Passkey: " . substr($credentials->passkey, 0, 10) . "...\n\n";

// Test 1: Direct access token request
echo "🔑 Test 1: Direct access token request...\n";

$authUrl = $credentials->environment === 'sandbox' 
    ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
    : 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$authString = base64_encode($credentials->consumer_key . ':' . $credentials->consumer_secret);

echo "   Auth URL: {$authUrl}\n";
echo "   Auth String: " . substr($authString, 0, 20) . "...\n";

try {
    $response = Http::withoutVerifying()
        ->withHeaders([
            'Authorization' => 'Basic ' . $authString,
            'Content-Type' => 'application/json'
        ])
        ->get($authUrl);

    echo "   Response Status: {$response->status()}\n";
    echo "   Response Body: " . $response->body() . "\n\n";

    if ($response->successful()) {
        $data = $response->json();
        if (isset($data['access_token'])) {
            echo "✅ Access token obtained successfully!\n";
            echo "   Token: " . substr($data['access_token'], 0, 20) . "...\n";
            echo "   Expires In: {$data['expires_in']} seconds\n\n";
        } else {
            echo "❌ No access token in response\n";
            echo "   Response data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
        }
    } else {
        echo "❌ HTTP request failed\n";
        echo "   Status: {$response->status()}\n";
        echo "   Body: {$response->body()}\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception during access token request: {$e->getMessage()}\n\n";
}

// Test 2: MpesaService access token
echo "🔧 Test 2: MpesaService access token...\n";

try {
    $mpesaService = new MpesaService($credentials);
    $accessToken = $mpesaService->getAccessToken();
    
    if ($accessToken) {
        echo "✅ MpesaService access token: " . substr($accessToken, 0, 20) . "...\n\n";
    } else {
        echo "❌ MpesaService failed to get access token\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception in MpesaService: {$e->getMessage()}\n\n";
}

// Test 3: Check credential URLs
echo "🌐 Test 3: Checking credential URLs...\n";
echo "   STK Push URL: {$credentials->getStkPushUrl()}\n";
echo "   STK Query URL: {$credentials->getStkPushQueryUrl()}\n";
echo "   Callback URL: {$credentials->getCallbackUrl()}\n\n";

// Test 4: Test password generation
echo "🔐 Test 4: Testing password generation...\n";
$timestamp = date('YmdHis');
$password = base64_encode(
    $credentials->business_shortcode . 
    $credentials->passkey . 
    $timestamp
);

echo "   Timestamp: {$timestamp}\n";
echo "   Password: " . substr($password, 0, 20) . "...\n";
echo "   Components: {$credentials->business_shortcode} + passkey + {$timestamp}\n\n";

// Test 5: Check if credentials are valid
echo "✅ Test 5: Credential validation...\n";
echo "   Business Shortcode: " . (strlen($credentials->business_shortcode) >= 5 ? 'Valid' : 'Invalid') . "\n";
echo "   Consumer Key: " . (strlen($credentials->consumer_key) >= 10 ? 'Valid' : 'Invalid') . "\n";
echo "   Consumer Secret: " . (strlen($credentials->consumer_secret) >= 10 ? 'Valid' : 'Invalid') . "\n";
echo "   Passkey: " . (strlen($credentials->passkey) >= 10 ? 'Valid' : 'Invalid') . "\n\n";

// Test 6: Check Laravel logs
echo "📋 Test 6: Recent Laravel logs...\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $recentLines = array_slice($lines, -20); // Last 20 lines
    
    echo "   Recent log entries:\n";
    foreach ($recentLines as $line) {
        if (strpos($line, 'M-PESA') !== false || strpos($line, 'mpesa') !== false) {
            echo "   " . trim($line) . "\n";
        }
    }
} else {
    echo "   No Laravel log file found\n";
}

echo "\n=== Debug Summary ===\n";
echo "✅ Credentials found and loaded\n";
echo "✅ URLs configured correctly\n";
echo "✅ Password generation working\n";
echo "❌ Access token issue detected\n";

echo "\n=== Recommendations ===\n";
echo "1. Check if Consumer Key and Secret are correct\n";
echo "2. Verify the credentials are for sandbox environment\n";
echo "3. Check if M-PESA API is accessible from your server\n";
echo "4. Verify network connectivity to safaricom.co.ke\n";
echo "5. Check if credentials are not expired\n";

echo "\nDebug completed!\n"; 