<?php

require_once 'vendor/autoload.php';

use App\Models\PaymentCallbackResponse;
use App\Models\PaymentCallbackUrl;
use App\Models\BranchMpesaCredential;
use App\Services\MpesaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== M-PESA Payment Test with +254 111383064 ===\n\n";

// Test phone number
$testPhone = '254111383064'; // Remove + for M-PESA format
$testAmount = 1; // 1 KSH for testing

echo "📱 Test Phone: +254 111383064\n";
echo "💰 Test Amount: {$testAmount} KSH\n\n";

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
echo "   Callback URL: {$credentials->getCallbackUrl()}\n\n";

// Initialize M-PESA service
$mpesaService = new MpesaService($credentials);

// Step 1: Check for ongoing transactions
echo "🔍 Step 1: Checking for ongoing transactions...\n";
$ongoingCheck = $mpesaService->checkOngoingTransaction($testPhone);

if ($ongoingCheck['success']) {
    echo "✅ No ongoing transactions detected\n";
} else {
    if ($ongoingCheck['has_ongoing_transaction'] ?? false) {
        echo "⚠️  Ongoing transaction detected: {$ongoingCheck['message']}\n";
        echo "   Waiting 30 seconds before proceeding...\n";
        sleep(30);
    } else {
        echo "❌ Error checking ongoing transactions: {$ongoingCheck['message']}\n";
    }
}

// Step 2: Initiate STK Push
echo "\n🚀 Step 2: Initiating STK Push...\n";
$stkResult = $mpesaService->initiateStkPush($testPhone, $testAmount, 'Test Payment');

if ($stkResult['success']) {
    echo "✅ STK Push initiated successfully!\n";
    echo "   Merchant Request ID: {$stkResult['merchant_request_id']}\n";
    echo "   Checkout Request ID: {$stkResult['checkout_request_id']}\n";
    echo "   Data: " . json_encode($stkResult['data'], JSON_PRETTY_PRINT) . "\n\n";
    
    $checkoutRequestId = $stkResult['checkout_request_id'];
    
    // Step 3: Wait for callback response
    echo "⏳ Step 3: Waiting for callback response...\n";
    echo "   Please complete or cancel the payment on your phone.\n";
    echo "   Waiting for M-PESA to send the final callback...\n\n";
    
    // Step 4: Wait for callback response
    echo "🔄 Step 4: Monitoring for callback response...\n";
    $maxWaitTime = 120; // Wait up to 2 minutes
    $checkInterval = 2; // Check every 2 seconds
    $elapsedTime = 0;
    $callbackReceived = false;
    
    while ($elapsedTime < $maxWaitTime && !$callbackReceived) {
        // Check for new callback responses
        $responses = PaymentCallbackResponse::where('checkout_request_id', $checkoutRequestId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        if ($responses->count() > 0) {
            $callbackReceived = true;
            $latestResponse = $responses->first();
            
            echo "🎉 CALLBACK RECEIVED!\n";
            echo "   Response ID: {$latestResponse->id}\n";
            echo "   Status: {$latestResponse->status}\n";
            echo "   Result Code: {$latestResponse->result_code}\n";
            echo "   Result Description: {$latestResponse->result_desc}\n";
            
            if ($latestResponse->amount) {
                echo "   💰 Amount: {$latestResponse->amount} KSH\n";
            }
            if ($latestResponse->mpesa_receipt_number) {
                echo "   🧾 Receipt Number: {$latestResponse->mpesa_receipt_number}\n";
            }
            if ($latestResponse->phone_number) {
                echo "   📱 Phone Number: {$latestResponse->phone_number}\n";
            }
            
            echo "   ⏰ Received at: {$latestResponse->created_at->format('Y-m-d H:i:s')}\n";
            
            // Show what happened
            switch ($latestResponse->result_code) {
                case 0:
                    echo "\n✅ PAYMENT SUCCESSFUL!\n";
                    echo "   Amount: {$latestResponse->amount} KSH\n";
                    echo "   Receipt: {$latestResponse->mpesa_receipt_number}\n";
                    echo "   Phone: {$latestResponse->phone_number}\n";
                    break;
                    
                case 1032:
                    echo "\n❌ PAYMENT CANCELLED BY USER\n";
                    echo "   The user cancelled the payment on their phone\n";
                    break;
                    
                case 1037:
                    echo "\n⏰ PAYMENT TIMEOUT\n";
                    echo "   The user did not respond to the STK push in time\n";
                    break;
                    
                case 1:
                    echo "\n❌ INSUFFICIENT BALANCE\n";
                    echo "   The user has insufficient funds in their M-PESA account\n";
                    break;
                    
                default:
                    echo "\n❌ PAYMENT FAILED\n";
                    echo "   Error Code: {$latestResponse->result_code}\n";
                    echo "   Reason: {$latestResponse->result_desc}\n";
                    break;
            }
            
            break;
        }
        
        // Show progress
        if ($elapsedTime % 10 == 0) {
            echo "   ⏳ Still waiting... ({$elapsedTime}s elapsed)\n";
        }
        
        sleep($checkInterval);
        $elapsedTime += $checkInterval;
    }
    
    if (!$callbackReceived) {
        echo "\n⏰ TIMEOUT: No callback received within {$maxWaitTime} seconds\n";
        echo "   The payment may still be processing or the callback was not sent\n";
    }
    
} else {
    echo "❌ STK Push failed!\n";
    echo "   Error: {$stkResult['message']}\n";
    if (isset($stkResult['error_code'])) {
        echo "   Error Code: {$stkResult['error_code']}\n";
    }
    if (isset($stkResult['error_type'])) {
        echo "   Error Type: {$stkResult['error_type']}\n";
    }
    
    // Set a default value for checkoutRequestId when STK push fails
    $checkoutRequestId = 'failed_' . time();
}

// Step 5: Final transaction summary
echo "\n📊 Step 5: Final Transaction Summary\n";
echo "   Checkout Request ID: {$checkoutRequestId}\n";
echo "   Phone Number: {$testPhone}\n";
echo "   Test Amount: {$testAmount} KSH\n";

$responses = PaymentCallbackResponse::where('checkout_request_id', $checkoutRequestId)
    ->orderBy('created_at', 'desc')
    ->get();

if ($responses->count() > 0) {
    $finalResponse = $responses->first();
    echo "   Final Status: {$finalResponse->status}\n";
    echo "   Result Code: {$finalResponse->result_code}\n";
    echo "   Result Description: {$finalResponse->result_desc}\n";
    
    if ($finalResponse->amount) {
        echo "   💰 Amount Paid: {$finalResponse->amount} KSH\n";
    }
    if ($finalResponse->mpesa_receipt_number) {
        echo "   🧾 Receipt Number: {$finalResponse->mpesa_receipt_number}\n";
    }
    if ($finalResponse->transaction_date) {
        echo "   📅 Transaction Date: {$finalResponse->transaction_date}\n";
    }
    
    echo "   ⏰ Callback Received: {$finalResponse->created_at->format('Y-m-d H:i:s')}\n";
} else {
    echo "   ❌ No callback response received\n";
    echo "   The transaction may still be pending or the callback was not sent\n";
}

// Step 6: Check all recent callback responses
echo "\n📈 Step 6: Recent callback responses (last 10)...\n";
$recentResponses = PaymentCallbackResponse::orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

echo "   Total responses in database: " . PaymentCallbackResponse::count() . "\n";
echo "   Recent responses:\n";

foreach ($recentResponses as $response) {
    echo "   - ID: {$response->id}, Status: {$response->status}, Result Code: {$response->result_code}\n";
    echo "     Created: {$response->created_at->format('Y-m-d H:i:s')}\n";
    echo "     Phone: {$response->phone_number}\n";
}

// Step 7: Check callback URLs
echo "\n🔗 Step 7: Callback URL status...\n";
$callbackUrls = PaymentCallbackUrl::all();

foreach ($callbackUrls as $url) {
    echo "   - ID: {$url->id}, Type: {$url->payment_type}, Provider: {$url->provider}\n";
    echo "     URL: {$url->callback_url}\n";
    echo "     Active: " . ($url->is_active ? 'Yes' : 'No') . ", Verified: " . ($url->is_verified ? 'Yes' : 'No') . "\n";
    echo "     Last Callback: " . ($url->last_callback_at ? $url->last_callback_at->format('Y-m-d H:i:s') : 'Never') . "\n";
}

echo "\n=== Test Summary ===\n";
echo "✅ M-PESA credentials: Configured\n";
echo "✅ Callback URLs: " . PaymentCallbackUrl::count() . " configured\n";
echo "✅ Callback Responses: " . PaymentCallbackResponse::count() . " total\n";
echo "✅ Test completed for phone: +254 111383064\n";

echo "\n=== Next Steps ===\n";
echo "1. Check your phone for STK push prompt\n";
echo "2. Complete or cancel the payment on your phone\n";
echo "3. Run this script again to see callback responses\n";
echo "4. Check Laravel logs for callback activity\n";
$appUrl = env('APP_URL', 'http://127.0.0.1:8000');
echo "5. Callback URL: {$appUrl}/api/mpesa/callback\n";

echo "\nTest completed!\n"; 