<?php

require_once 'vendor/autoload.php';

use App\Services\MpesaService;
use App\Models\BranchMpesaCredential;

// Test the checkOngoingTransaction method
echo "Testing M-PESA checkOngoingTransaction method...\n";

try {
    // Get the first available credentials
    $credentials = BranchMpesaCredential::where('is_active', 1)->first();
    
    if (!$credentials) {
        echo "No active M-PESA credentials found in database.\n";
        exit(1);
    }
    
    echo "Using credentials for branch ID: " . $credentials->branch_id . "\n";
    echo "Environment: " . $credentials->environment . "\n";
    
    $mpesaService = new MpesaService($credentials);
    
    // Test with a sample phone number
    $testPhone = '254708374149';
    echo "Testing with phone: " . $testPhone . "\n";
    
    $result = $mpesaService->checkOngoingTransaction($testPhone);
    
    echo "Result:\n";
    print_r($result);
    
    // Check if the result has the expected structure
    if (is_array($result) && isset($result['success']) && isset($result['has_ongoing_transaction'])) {
        echo "\n✅ SUCCESS: Result has correct structure\n";
    } else {
        echo "\n❌ ERROR: Result missing expected keys\n";
        echo "Expected keys: success, has_ongoing_transaction\n";
        echo "Actual keys: " . (is_array($result) ? implode(', ', array_keys($result)) : 'not an array') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n"; 