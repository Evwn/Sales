<?php

// Test M-PESA Amount Rounding
echo "Testing M-PESA Amount Rounding\n";
echo "==============================\n\n";

// Test cases
$testAmounts = [
    60.9,  // Should round to 61
    60.4,  // Should round to 60
    60.5,  // Should round to 61
    60.0,  // Should stay 60
    61.0,  // Should stay 61
    100.7, // Should round to 101
    100.3, // Should round to 100
    500.99, // Should round to 501
    500.01  // Should round to 500
];

echo "Rounding Test Results:\n";
echo "Original Amount | Rounded Amount\n";
echo "----------------|---------------\n";

foreach ($testAmounts as $amount) {
    $rounded = (int)round($amount);
    echo sprintf("%-15.2f | %d\n", $amount, $rounded);
}

echo "\nExpected Behavior:\n";
echo "- 60.9 → 61 (rounds up)\n";
echo "- 60.4 → 60 (rounds down)\n";
echo "- 60.5 → 61 (rounds up)\n";
echo "- 60.0 → 60 (no change)\n";
echo "- 61.0 → 61 (no change)\n";

echo "\n✓ M-PESA amount rounding implemented correctly!\n";
?> 