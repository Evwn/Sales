<?php

require_once 'vendor/autoload.php';

use App\Models\PaymentCallbackResponse;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== M-PESA Callback Watcher ===\n";
echo "Watching for new callback responses...\n";
echo "Press Ctrl+C to stop\n\n";

$lastCount = PaymentCallbackResponse::count();
echo "Initial callback count: {$lastCount}\n\n";

while (true) {
    $currentCount = PaymentCallbackResponse::count();
    
    if ($currentCount > $lastCount) {
        echo "🎉 NEW CALLBACK RECEIVED!\n";
        
        // Get the latest response
        $latestResponse = PaymentCallbackResponse::orderBy('created_at', 'desc')->first();
        
        echo "📋 Response ID: {$latestResponse->id}\n";
        echo "📱 Phone: {$latestResponse->phone_number}\n";
        echo "💰 Amount: {$latestResponse->amount} KSH\n";
        echo "📊 Status: {$latestResponse->status}\n";
        echo "🔢 Result Code: {$latestResponse->result_code}\n";
        echo "📝 Result Description: {$latestResponse->result_desc}\n";
        
        if ($latestResponse->mpesa_receipt_number) {
            echo "🧾 Receipt: {$latestResponse->mpesa_receipt_number}\n";
        }
        
        echo "⏰ Time: {$latestResponse->created_at->format('Y-m-d H:i:s')}\n";
        
        // Show what happened
        switch ($latestResponse->result_code) {
            case 0:
                echo "✅ PAYMENT SUCCESSFUL!\n";
                echo "   Amount: {$latestResponse->amount} KSH\n";
                echo "   Receipt: {$latestResponse->mpesa_receipt_number}\n";
                echo "   Phone: {$latestResponse->phone_number}\n";
                break;
                
            case 1032:
                echo "❌ PAYMENT CANCELLED BY USER\n";
                echo "   The user cancelled the payment on their phone\n";
                break;
                
            case 1037:
                echo "⏰ PAYMENT TIMEOUT\n";
                echo "   The user did not respond to the STK push in time\n";
                break;
                
            case 1:
                echo "❌ INSUFFICIENT BALANCE\n";
                echo "   The user has insufficient funds in their M-PESA account\n";
                break;
                
            default:
                echo "❌ PAYMENT FAILED\n";
                echo "   Error Code: {$latestResponse->result_code}\n";
                echo "   Reason: {$latestResponse->result_desc}\n";
                break;
        }
        
        echo "\n" . str_repeat("=", 50) . "\n\n";
        
        $lastCount = $currentCount;
    }
    
    sleep(2); // Check every 2 seconds
} 