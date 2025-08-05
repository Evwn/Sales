<?php

echo "=== Testing Callback URL Accessibility ===\n\n";

$callbackUrl = 'https://31642a4b1188.ngrok-free.app/api/mpesa/callback';

echo "🔗 Testing URL: {$callbackUrl}\n\n";

// Test 1: Simple GET request
echo "📡 Test 1: GET Request\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$response = file_get_contents($callbackUrl, false, $context);
$httpCode = $http_response_header[0] ?? 'Unknown';

echo "   HTTP Response: {$httpCode}\n";
echo "   Response Length: " . strlen($response) . " bytes\n";
echo "   Response Preview: " . substr($response, 0, 200) . "...\n\n";

// Test 2: POST request with M-PESA-like data
echo "📡 Test 2: POST Request (M-PESA Callback Simulation)\n";

$postData = [
    'Body' => [
        'stkCallback' => [
            'MerchantRequestID' => 'test-123',
            'CheckoutRequestID' => 'ws_CO_test_123',
            'ResultCode' => 1032,
            'ResultDesc' => 'Request canceled by user.'
        ]
    ]
];

$postDataJson = json_encode($postData);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postDataJson)
        ],
        'content' => $postDataJson,
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$response = file_get_contents($callbackUrl, false, $context);
$httpCode = $http_response_header[0] ?? 'Unknown';

echo "   HTTP Response: {$httpCode}\n";
echo "   Response Length: " . strlen($response) . " bytes\n";
echo "   Response: {$response}\n\n";

// Test 3: Check if ngrok is working
echo "📡 Test 3: Ngrok Status Check\n";
$ngrokUrl = 'https://31642a4b1188.ngrok-free.app/';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 5,
        'ignore_errors' => true
    ]
]);

$response = file_get_contents($ngrokUrl, false, $context);
$httpCode = $http_response_header[0] ?? 'Unknown';

echo "   Ngrok Base URL: {$ngrokUrl}\n";
echo "   HTTP Response: {$httpCode}\n";
echo "   Response Length: " . strlen($response) . " bytes\n\n";

echo "=== Test Results ===\n";
if (strpos($httpCode, '200') !== false) {
    echo "✅ Callback URL is accessible\n";
} else {
    echo "❌ Callback URL is NOT accessible\n";
    echo "   This is why M-PESA cannot send callbacks\n";
    echo "   Please check your ngrok tunnel\n";
}

echo "\nTest completed!\n"; 