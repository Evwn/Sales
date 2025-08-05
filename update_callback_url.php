<?php

require_once 'vendor/autoload.php';

use App\Models\PaymentCallbackUrl;
use App\Models\BranchMpesaCredential;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Updating Callback URL ===\n\n";

$newCallbackUrl = 'https://31642a4b1188.ngrok-free.app/api/mpesa/callback';

// Update PaymentCallbackUrl records
$callbackUrls = PaymentCallbackUrl::where('payment_type', 'mpesa')->get();

foreach ($callbackUrls as $url) {
    $oldUrl = $url->callback_url;
    $url->callback_url = $newCallbackUrl;
    $url->save();
    
    echo "✅ Updated PaymentCallbackUrl ID: {$url->id}\n";
    echo "   Old URL: {$oldUrl}\n";
    echo "   New URL: {$url->callback_url}\n\n";
}

// Update BranchMpesaCredential callback URLs
$credentials = BranchMpesaCredential::where('is_active', true)->get();

foreach ($credentials as $credential) {
    $callbackUrl = $credential->callbackUrl;
    if ($callbackUrl) {
        $oldUrl = $callbackUrl->callback_url;
        $callbackUrl->callback_url = $newCallbackUrl;
        $callbackUrl->save();
        
        echo "✅ Updated BranchMpesaCredential ID: {$credential->id}\n";
        echo "   Old URL: {$oldUrl}\n";
        echo "   New URL: {$callbackUrl->callback_url}\n\n";
    }
}

echo "=== Callback URL Update Complete ===\n";
echo "New callback URL: {$newCallbackUrl}\n"; 