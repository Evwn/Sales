<?php

require_once 'vendor/autoload.php';

use App\Models\PaymentCallbackResponse;
use App\Models\PaymentCallbackUrl;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== M-PESA Callback Monitor ===\n\n";

echo "🔗 Callback URL: https://31642a4b1188.ngrok-free.app/api/mpesa/callback\n\n";

echo "📊 Current Callback Status:\n";
$callbackUrls = PaymentCallbackUrl::all();

foreach ($callbackUrls as $url) {
    echo "   - ID: {$url->id}, Type: {$url->payment_type}, Provider: {$url->provider}\n";
    echo "     URL: {$url->callback_url}\n";
    echo "     Active: " . ($url->is_active ? 'Yes' : 'No') . ", Verified: " . ($url->is_verified ? 'Yes' : 'No') . "\n";
    echo "     Last Callback: " . ($url->last_callback_at ? $url->last_callback_at->format('Y-m-d H:i:s') : 'Never') . "\n\n";
}

echo "📈 Recent Callback Responses (last 20):\n";
$recentResponses = PaymentCallbackResponse::orderBy('created_at', 'desc')
    ->limit(20)
    ->get();

echo "   Total responses in database: " . PaymentCallbackResponse::count() . "\n\n";

if ($recentResponses->count() === 0) {
    echo "   No callback responses yet. Waiting for M-PESA to send callbacks...\n\n";
} else {
    foreach ($recentResponses as $response) {
        echo "   📋 Response ID: {$response->id}\n";
        echo "      Status: {$response->status}\n";
        echo "      Result Code: {$response->result_code}\n";
        echo "      Result Description: {$response->result_desc}\n";
        echo "      Merchant Request ID: {$response->merchant_request_id}\n";
        echo "      Checkout Request ID: {$response->checkout_request_id}\n";
        
        if ($response->amount) {
            echo "      💰 Amount: {$response->amount} KSH\n";
        }
        if ($response->mpesa_receipt_number) {
            echo "      🧾 Receipt Number: {$response->mpesa_receipt_number}\n";
        }
        if ($response->phone_number) {
            echo "      📱 Phone Number: {$response->phone_number}\n";
        }
        if ($response->transaction_date) {
            echo "      📅 Transaction Date: {$response->transaction_date}\n";
        }
        if ($response->balance) {
            echo "      💳 Balance: {$response->balance} KSH\n";
        }
        
        echo "      Created: {$response->created_at->format('Y-m-d H:i:s')}\n";
        echo "      Processed: " . ($response->is_processed ? 'Yes' : 'No') . "\n";
        
        // Show what the callback means
        $statusDescription = $response->getStatusDescription();
        echo "      📝 Status: {$statusDescription}\n";
        
        if ($response->callback_data) {
            echo "      📄 Raw Callback Data:\n";
            $formattedData = json_encode($response->callback_data, JSON_PRETTY_PRINT);
            $lines = explode("\n", $formattedData);
            foreach ($lines as $line) {
                echo "         {$line}\n";
            }
        }
        
        echo "\n";
    }
}

echo "=== Callback Information ===\n";
echo "The callback URL receives the following information from M-PESA:\n\n";

echo "✅ SUCCESSFUL PAYMENT (ResultCode: 0):\n";
echo "   - Amount paid\n";
echo "   - M-PESA Receipt Number\n";
echo "   - Transaction Date & Time\n";
echo "   - Phone Number used\n";
echo "   - Account Balance after payment\n";
echo "   - Merchant Request ID\n";
echo "   - Checkout Request ID\n\n";

echo "❌ FAILED PAYMENT (ResultCode: 1-9999):\n";
echo "   - Error code and description\n";
echo "   - Reason for failure\n";
echo "   - Merchant Request ID\n";
echo "   - Checkout Request ID\n\n";

echo "⏰ TIMEOUT/CANCELED (ResultCode: 1032, 1037, etc.):\n";
echo "   - Timeout reason\n";
echo "   - Cancel reason\n";
echo "   - Merchant Request ID\n";
echo "   - Checkout Request ID\n\n";

echo "=== How to Monitor ===\n";
echo "1. Run this script to see all callback responses\n";
echo "2. Check Laravel logs: tail -f storage/logs/laravel.log\n";
echo "3. The callback URL automatically saves all responses to database\n";
echo "4. Each response contains complete payment details\n\n";

echo "=== Example Callback Data Structure ===\n";
echo "SUCCESS:\n";
echo "{\n";
echo "  \"Body\": {\n";
echo "    \"stkCallback\": {\n";
echo "      \"MerchantRequestID\": \"29115-34620561-1\",\n";
echo "      \"CheckoutRequestID\": \"ws_CO_191220191020363925\",\n";
echo "      \"ResultCode\": 0,\n";
echo "      \"ResultDesc\": \"The service request is processed successfully.\",\n";
echo "      \"CallbackMetadata\": {\n";
echo "        \"Item\": [\n";
echo "          {\"Name\": \"Amount\", \"Value\": 1.00},\n";
echo "          {\"Name\": \"MpesaReceiptNumber\", \"Value\": \"NLJ7RT61SV\"},\n";
echo "          {\"Name\": \"TransactionDate\", \"Value\": 20191219102115},\n";
echo "          {\"Name\": \"PhoneNumber\", \"Value\": 254708374149}\n";
echo "        ]\n";
echo "      }\n";
echo "    }\n";
echo "  }\n";
echo "}\n\n";

echo "FAILED:\n";
echo "{\n";
echo "  \"Body\": {\n";
echo "    \"stkCallback\": {\n";
echo "      \"MerchantRequestID\": \"29115-34620561-1\",\n";
echo "      \"CheckoutRequestID\": \"ws_CO_191220191020363925\",\n";
echo "      \"ResultCode\": 1032,\n";
echo "      \"ResultDesc\": \"Request canceled by user.\"\n";
echo "    }\n";
echo "  }\n";
echo "}\n\n";

echo "Monitor completed!\n"; 