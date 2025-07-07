<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\App;

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Set your test email address here:
$to = getenv('MAIL_TEST_TO') ?: 'erastuseven02@gmail.com';

try {
    Mail::raw('This is a test email from Sales Management System (test_mail.php)', function($m) use ($to) {
        $m->to($to)->subject('Test Email from Sales Management System');
    });
    echo "Test email sent to $to.\n";
} catch (Exception $e) {
    echo "Error sending test email: " . $e->getMessage() . "\n";
} 