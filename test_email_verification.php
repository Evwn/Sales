<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING EMAIL VERIFICATION TOGGLE ===\n";

// Get Jane's user record
$jane = \App\Models\User::where('email', 'jane002683158963@gmail.com')->first();

if (!$jane) {
    echo "Jane not found!\n";
    exit;
}

echo "Before toggle:\n";
echo "Name: {$jane->name}\n";
echo "Email: {$jane->email}\n";
echo "Email verified at: " . ($jane->email_verified_at ? $jane->email_verified_at : 'NULL') . "\n";

// Toggle the email verification
$newStatus = $jane->email_verified_at ? null : now();
echo "\nNew status will be: " . ($newStatus ? $newStatus : 'NULL') . "\n";

// Update the user
$jane->update([
    'email_verified_at' => $newStatus,
]);

// Refresh the model
$jane->refresh();

echo "\nAfter toggle:\n";
echo "Name: {$jane->name}\n";
echo "Email: {$jane->email}\n";
echo "Email verified at: " . ($jane->email_verified_at ? $jane->email_verified_at : 'NULL') . "\n";

echo "\nToggle completed successfully!\n"; 