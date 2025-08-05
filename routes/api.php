<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// PesaPal API routes (no CSRF protection)
Route::prefix('pesapal')->group(function () {
    Route::post('/callback', function(Request $request) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] API CALLBACK RECEIVED\n";
        $log_entry .= "Method: " . $request->method() . "\n";
        $log_entry .= "URL: " . $request->fullUrl() . "\n";
        $log_entry .= "Headers: " . json_encode($request->headers->all()) . "\n";
        $log_entry .= "Query Params: " . json_encode($request->query->all()) . "\n";
        $log_entry .= "Body: " . json_encode($request->all()) . "\n";
        $log_entry .= "Raw Body: " . $request->getContent() . "\n";
        $log_entry .= "---\n";
        
        file_put_contents('pesapal_callbacks.log', $log_entry, FILE_APPEND);
        
        return response()->json(['status' => 'success', 'message' => 'Callback received']);
    });
    
    Route::get('/callback', function(Request $request) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] API CALLBACK RECEIVED (GET)\n";
        $log_entry .= "Method: " . $request->method() . "\n";
        $log_entry .= "URL: " . $request->fullUrl() . "\n";
        $log_entry .= "Headers: " . json_encode($request->headers->all()) . "\n";
        $log_entry .= "Query Params: " . json_encode($request->query->all()) . "\n";
        $log_entry .= "---\n";
        
        file_put_contents('pesapal_callbacks.log', $log_entry, FILE_APPEND);
        
        return response()->json(['status' => 'success', 'message' => 'Callback received']);
    });
});

// M-PESA API routes (no CSRF protection)
Route::prefix('mpesa')->group(function () {
    Route::post('/callback', [\App\Http\Controllers\Settings\PaymentTypesController::class, 'callback']);
Route::get('/callback', function(Request $request) {
    return response()->json(['status' => 'success', 'message' => 'M-PESA callback endpoint is working']);
});

  // POS M-PESA Callback Routes
  Route::post('/pos/mpesa/callback', [\App\Http\Controllers\POSMpesaController::class, 'handleCallback']);
  Route::post('/pos/mpesa/callback/business-{businessId}-branch-{branchId}', [\App\Http\Controllers\POSMpesaController::class, 'handleCallback']);
  });