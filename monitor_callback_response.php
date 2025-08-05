<?php

// Bootstrap Laravel
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PaymentCallbackResponse;
use App\Models\PaymentCallbackUrl;

echo "🔍 M-PESA Callback Response Monitor\n";
echo "=====================================\n\n";

// Get the latest callback response for the specific checkout request ID
$checkoutRequestId = 'ws_CO_030820250238498111383064';

echo "Looking for callback response for Checkout Request ID: {$checkoutRequestId}\n\n";

// Function to display callback response details
function displayCallbackResponse($response) {
    echo "📱 CALLBACK RESPONSE RECEIVED!\n";
    echo "==============================\n";
    echo "Status: " . ($response->status ?? 'Unknown') . "\n";
    echo "Result Code: " . ($response->result_code ?? 'N/A') . "\n";
    echo "Result Description: " . ($response->result_desc ?? 'N/A') . "\n";
    echo "Amount: KES " . ($response->amount ?? 'N/A') . "\n";
    echo "Phone Number: " . ($response->phone_number ?? 'N/A') . "\n";
    echo "M-PESA Receipt Number: " . ($response->mpesa_receipt_number ?? 'N/A') . "\n";
    echo "Transaction Date: " . ($response->transaction_date ?? 'N/A') . "\n";
    echo "Balance: " . ($response->balance ?? 'N/A') . "\n";
    echo "Is Processed: " . ($response->is_processed ? 'Yes' : 'No') . "\n";
    echo "Processed At: " . ($response->processed_at ?? 'Not processed yet') . "\n";
    
    if ($response->error_message) {
        echo "Error Message: " . $response->error_message . "\n";
    }
    
    echo "\n📋 Full Callback Data:\n";
    echo "=====================\n";
    if ($response->callback_data) {
        $callbackData = is_string($response->callback_data) ? json_decode($response->callback_data, true) : $response->callback_data;
        print_r($callbackData);
    } else {
        echo "No callback data available\n";
    }
    
    echo "\n📊 Headers:\n";
    echo "===========\n";
    if ($response->headers) {
        $headers = is_string($response->headers) ? json_decode($response->headers, true) : $response->headers;
        print_r($headers);
    } else {
        echo "No headers available\n";
    }
}

try {
    // Check if callback response exists
    $callbackResponse = PaymentCallbackResponse::where('checkout_request_id', $checkoutRequestId)->first();

    if ($callbackResponse) {
        echo "✅ Found callback response!\n\n";
        displayCallbackResponse($callbackResponse);
    } else {
        echo "⏳ No callback response found yet. Waiting for callback...\n\n";
        
        // Monitor for new callback responses
        $lastCheck = time();
        $maxWaitTime = 300; // 5 minutes
        $checkInterval = 2; // Check every 2 seconds
        
        echo "Monitoring for callback responses... (will wait up to 5 minutes)\n";
        echo "Press Ctrl+C to stop monitoring\n\n";
        
        while ((time() - $lastCheck) < $maxWaitTime) {
            // Check for any new callback responses
            $newResponses = PaymentCallbackResponse::where('created_at', '>', date('Y-m-d H:i:s', $lastCheck))->get();
            
            if ($newResponses->count() > 0) {
                echo "\n🎉 NEW CALLBACK RESPONSE(S) RECEIVED!\n";
                echo "=====================================\n\n";
                
                foreach ($newResponses as $response) {
                    echo "Checkout Request ID: " . $response->checkout_request_id . "\n";
                    echo "Result Code: " . $response->result_code . "\n";
                    echo "Result Description: " . $response->result_desc . "\n";
                    echo "Received at: " . $response->created_at . "\n";
                    echo "---\n";
                    
                    // If this is the one we're looking for, display full details
                    if ($response->checkout_request_id === $checkoutRequestId) {
                        echo "\n🎯 MATCHING CALLBACK FOUND!\n";
                        displayCallbackResponse($response);
                        exit(0);
                    }
                }
            }
            
            // Check specifically for our checkout request ID
            $matchingResponse = PaymentCallbackResponse::where('checkout_request_id', $checkoutRequestId)->first();
            if ($matchingResponse) {
                echo "\n🎯 CALLBACK RESPONSE FOUND FOR YOUR TRANSACTION!\n";
                displayCallbackResponse($matchingResponse);
                exit(0);
            }
            
            echo "⏳ Still waiting... (" . (time() - $lastCheck) . "s elapsed)\n";
            sleep($checkInterval);
        }
        
        echo "\n⏰ Timeout reached. No callback response received within 5 minutes.\n";
        echo "This could mean:\n";
        echo "- The user hasn't completed the payment on their phone yet\n";
        echo "- The payment was cancelled or timed out\n";
        echo "- There's an issue with the callback URL\n";
        echo "- The callback hasn't been processed yet\n\n";
        
        // Show all recent callback responses
        echo "📋 Recent Callback Responses (last 10):\n";
        echo "=======================================\n";
        $recentResponses = PaymentCallbackResponse::orderBy('created_at', 'desc')->limit(10)->get();
        
        if ($recentResponses->count() > 0) {
            foreach ($recentResponses as $response) {
                echo "ID: " . $response->id . "\n";
                echo "Checkout Request ID: " . $response->checkout_request_id . "\n";
                echo "Result Code: " . $response->result_code . "\n";
                echo "Result Description: " . $response->result_desc . "\n";
                echo "Amount: KES " . $response->amount . "\n";
                echo "Phone: " . $response->phone_number . "\n";
                echo "Created: " . $response->created_at . "\n";
                echo "---\n";
            }
        } else {
            echo "No callback responses found in database.\n";
        }
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ Monitoring completed.\n"; 