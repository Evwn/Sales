<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentTestController extends Controller
{
    public function testMpesa()
    {
        $response = Http::withToken(env('FLW_SECRET_KEY'))
            ->post('https://api.flutterwave.com/v3/charges?type=mpesa', [
                'tx_ref' => uniqid('test_'),
                'amount' => 1500,
                'currency' => 'KES',
                'redirect_url' => 'https://yourapp.com/payment/callback',
                'payment_type' => 'mpesa',
                'phone_number' => '254709929220', // or your test number
                'email' => 'i@need.money',
            ]);
        return $response->json();
    }
} 