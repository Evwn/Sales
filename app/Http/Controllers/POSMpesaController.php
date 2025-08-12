<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\MpesaService;
use App\Models\BranchMpesaCredential;
use App\Models\PaymentCallbackResponse;
use App\Models\PosTicket;
use App\Models\PosTicketPayment;
use App\Models\PosDevice;
use App\Models\Branch;

class POSMpesaController extends Controller
{
    protected $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    /**
     * Initiate M-PESA STK Push for POS payment
     */
    public function initiateStkPush(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|integer|exists:pos_tickets,id',
            'amount' => 'required|numeric|min:1|max:100000',
            'phone' => 'required|string|min:10|max:12',
        ]);

        // Validate exact payment amount (±1 tolerance)
        $ticket = PosTicket::find($request->ticket_id);
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.'
            ], 400);
        }

        $amountDue = $ticket->amount_due;
        $requestedAmount = $request->amount;
        
        // Round off the requested amount to nearest whole number
        $roundedAmount = round($requestedAmount);
        
        // Calculate smart tolerance: round up to next 10 if decimal > 0.5, otherwise round down
        $decimal = fmod($amountDue, 1);
        $baseAmount = floor($amountDue);
        $smartTolerance = $decimal > 0.5 ? (10 - $decimal) : 0;
        $maxAllowed = $baseAmount + ($decimal > 0.5 ? 10 : $decimal);

        // Prevent payment amount from exceeding smart tolerance limit
        if ($roundedAmount > $maxAllowed) {
            return response()->json([
                'success' => false,
                'message' => "Payment amount cannot exceed " . number_format($maxAllowed, 2) . ". Requested amount: " . number_format($roundedAmount, 2)
            ], 400);
        }

        // Allow partial payments - no minimum amount requirement
        // Users can pay any amount from 0 to the amount due + tolerance
        if ($requestedAmount <= 0) {
            return response()->json([
                'success' => false,
                'message' => "Payment amount must be greater than 0. Requested amount: " . number_format($requestedAmount, 2)
            ], 400);
        }

        try {
            // Get device information from request header (same as check-device endpoint)
            $device_uuid = $request->header('X-Device-UUID');
            
            Log::info('POS M-PESA: Device UUID from header', [
                'device_uuid' => $device_uuid,
                'headers' => $request->headers->all()
            ]);
            
            if (!$device_uuid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device UUID not provided in header.'
                ], 400);
            }
            
            $device = PosDevice::where('device_uuid', $device_uuid)->first();
            
            Log::info('POS M-PESA: Device lookup result', [
                'device_uuid' => $device_uuid,
                'device_found' => $device ? true : false,
                'device_id' => $device ? $device->id : null,
                'branch_id' => $device ? $device->branch_id : null
            ]);
            
            // If device doesn't exist, create it with default branch (for testing)
            if (!$device) {
                // Get the first available branch for the current user's business
                $user = auth()->user();
                $branch = Branch::where('business_id', $user->business_id)->first();
                
                if (!$branch) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No branch found for this business. Please contact administrator.'
                    ], 400);
                }
                
                // Create the device record
                $device = PosDevice::create([
                    'device_uuid' => $device_uuid,
                    'business_id' => $branch->business_id,
                    'branch_id' => $branch->id,
                    'registered_by' => auth()->id(),
                    'is_disabled' => false
                ]);
                
                Log::info('POS M-PESA: Created new device', [
                    'device_uuid' => $device_uuid,
                    'device_id' => $device->id,
                    'branch_id' => $device->branch_id,
                    'business_id' => $device->business_id
                ]);
            }
            
            // Get M-PESA credentials for the device's branch (sandbox only for now)
            $credentials = BranchMpesaCredential::where('branch_id', $device->branch_id)
                ->where('environment', 'sandbox')
                ->where('is_active', true)
                ->first();

            if (!$credentials) {
                return response()->json([
                    'success' => false,
                    'message' => 'M-PESA credentials not found for this branch. Please configure M-PESA settings first.'
                ], 400);
            }

            // Format phone number
            $phone = $request->phone;
            
            // Validate Kenyan phone number format
            if (!preg_match('/^(254|0)[17]\d{8}$/', $phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number format. Please enter a valid Kenyan phone number (e.g., 0712345678 or 254712345678)'
                ], 400);
            }
            
            // Convert to 254 format if needed
            if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
                $phone = '254' . substr($phone, 1);
            }

            // Generate unique reference
            $reference = 'POS_' . $request->ticket_id . '_' . time();

            // Ensure callback URL exists for POS M-PESA payments
            $this->ensurePosCallbackUrlExists($credentials);
            
            // Create M-PESA service with the correct credentials for this branch
            $mpesaService = new \App\Services\MpesaService($credentials);
            

            
            // Initiate STK Push with POS payment type using rounded amount
            $response = $mpesaService->initiateStkPush(
                $phone,
                $roundedAmount,
                'POS Payment - Ticket #' . $request->ticket_id,
                'mpesa_pos' // Specify POS payment type for callback URL
            );

            if ($response['success']) {
                // Store test data in cache for callback reference
                $testData = [
                    'ticket_id' => $request->ticket_id,
                    'amount' => $roundedAmount,
                    'phone' => $phone,
                    'reference' => $reference,
                    'branch_id' => $device->branch_id,
                    'business_id' => $credentials->branch->business_id,
                    'timestamp' => now()->toISOString()
                ];

                Cache::put('mpesa_callback_' . $response['checkout_request_id'], $testData, 3600);

                // Create pending payment record with rounded amount
                PosTicketPayment::create([
                    'ticket_id' => $request->ticket_id,
                    'method' => 'mpesa',
                    'amount' => $roundedAmount,
                    'status' => 'pending',
                    'meta' => [
                        'checkout_request_id' => $response['checkout_request_id'],
                        'phone' => $phone,
                        'reference' => $reference,
                        'environment' => 'sandbox'
                    ]
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'M-PESA payment initiated successfully. Please check your phone and enter your M-PESA PIN.',
                    'status' => 'stk_sent',
                    'checkout_request_id' => $response['checkout_request_id'],
                    'merchant_request_id' => $response['merchant_request_id'] ?? null,
                    'data' => $response['data']
                ]);
            } else {
                // Provide user-friendly error messages for POS users
                $errorMessage = $this->getPosMpesaErrorMessage($response['message'], $response['error_code'] ?? null);
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error_type' => $this->getPosMpesaErrorType($response['message']),
                    'original_error' => $response['message'],
                    'error_code' => $response['error_code'] ?? null
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('POS M-PESA STK Push Error', [
                'ticket_id' => $request->ticket_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate M-PESA payment. Please try again.'
            ], 500);
        }
    }

    /**
     * Check M-PESA payment status (manual check only - no polling)
     */
    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'checkout_request_id' => 'required|string',
            'ticket_id' => 'required|integer|exists:pos_tickets,id'
        ]);

        try {
            // Get device to determine branch
            $device_uuid = $request->header('X-Device-UUID');
            
            Log::info('POS M-PESA Manual Check: Device UUID from header', [
                'device_uuid' => $device_uuid,
                'headers' => $request->headers->all()
            ]);
            
            $device = PosDevice::where('device_uuid', $device_uuid)->first();
            
            Log::info('POS M-PESA Manual Check: Device lookup result', [
                'device_uuid' => $device_uuid,
                'device_found' => $device ? true : false,
                'device_id' => $device ? $device->id : null,
                'branch_id' => $device ? $device->branch_id : null
            ]);
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 400);
            }
            
            // Check cache first for immediate response
            $cachedResponse = Cache::get('mpesa_callback_' . $request->checkout_request_id);
            if ($cachedResponse && isset($cachedResponse['callback_response'])) {
                $callbackData = $cachedResponse['callback_response'];
                $resultCode = $callbackData['result_code'] ?? null;
                $resultDesc = $callbackData['result_desc'] ?? '';
                $amount = $callbackData['amount'] ?? 'N/A';
                $phone = $callbackData['phone_number'] ?? 'N/A';
                
                return response()->json([
                    'success' => true,
                    'status' => $this->getPosMpesaCallbackStatus($resultCode),
                    'message' => $this->getPosMpesaCallbackMessage($resultCode, $resultDesc, $phone, $amount),
                    'callback_response' => $callbackData
                ]);
            }
            
            // Check for branch-specific callback broadcast
            $branchCallbackKey = "pos_mpesa_callback_branch_{$device->branch_id}";
            $branchCallback = Cache::get($branchCallbackKey);
            if ($branchCallback && $branchCallback['checkout_request_id'] === $request->checkout_request_id) {
                $resultCode = $branchCallback['result_code'] ?? null;
                $resultDesc = $branchCallback['result_desc'] ?? '';
                $amount = $branchCallback['amount'] ?? 'N/A';
                $phone = $branchCallback['phone_number'] ?? 'N/A';
                
                return response()->json([
                    'success' => true,
                    'status' => $this->getPosMpesaCallbackStatus($resultCode),
                    'message' => $this->getPosMpesaCallbackMessage($resultCode, $resultDesc, $phone, $amount),
                    'callback_response' => $branchCallback
                ]);
            }

            // Check database for stored callback
            $callbackResponse = PaymentCallbackResponse::where('checkout_request_id', $request->checkout_request_id)
                ->where(function($query) {
                    $query->where('business_id', auth()->user()->business_id)
                          ->orWhere('payment_type', 'mpesa_pos');
                })
                ->first();

            if ($callbackResponse) {
                // Update payment status in POS ticket
                $this->updatePosPaymentStatus($request->ticket_id, $callbackResponse);

                $resultCode = $callbackResponse->result_code;
                $resultDesc = $callbackResponse->result_desc;
                $amount = $callbackResponse->amount ?? 'N/A';
                $phone = $callbackResponse->phone_number ?? 'N/A';
                
                return response()->json([
                    'success' => true,
                    'status' => $this->getPosMpesaCallbackStatus($resultCode),
                    'message' => $this->getPosMpesaCallbackMessage($resultCode, $resultDesc, $phone, $amount),
                    'callback_response' => $callbackResponse
                ]);
            }

            // No callback response found yet
            return response()->json([
                'success' => true,
                'status' => 'pending',
                'message' => 'Payment initiated. Please complete the payment on your phone or refresh the page to check status.',
                'callback_response' => null
            ]);

        } catch (\Exception $e) {
            Log::error('POS M-PESA Manual Check Error', [
                'checkout_request_id' => $request->checkout_request_id,
                'ticket_id' => $request->ticket_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status'
            ], 500);
        }
    }

    /**
     * Handle M-PESA callback for POS payments
     */
    public function handleCallback(Request $request, $businessId = null, $branchId = null)
    {
        // Extract business and branch IDs from URL if not provided as parameters
        if (!$businessId || !$branchId) {
            $url = $request->url();
            if (preg_match('/business-(\d+)-branch-(\d+)/', $url, $matches)) {
                $businessId = $matches[1];
                $branchId = $matches[2];
            }
        }

        Log::info('POS M-PESA Callback Received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'business_id' => $businessId,
            'branch_id' => $branchId,
            'url' => $request->url()
        ]);

        try {
            $callbackData = $request->all();
            $checkoutRequestId = $callbackData['Body']['stkCallback']['CheckoutRequestID'] ?? null;

            if (!$checkoutRequestId) {
                Log::error('POS M-PESA Callback: Missing CheckoutRequestID');
                return response()->json(['success' => false, 'message' => 'Invalid callback data'], 400);
            }

            // Get cached test data
            $testData = Cache::get('mpesa_callback_' . $checkoutRequestId);
            if (!$testData) {
                Log::error('POS M-PESA Callback: No cached data found', ['checkout_request_id' => $checkoutRequestId]);
                return response()->json(['success' => false, 'message' => 'No cached data found'], 400);
            }

            // Validate that the callback is for the correct business/branch
            if ($businessId && $branchId) {
                if ($testData['business_id'] != $businessId || $testData['branch_id'] != $branchId) {
                    Log::error('POS M-PESA Callback: Business/Branch mismatch', [
                        'expected_business_id' => $testData['business_id'],
                        'expected_branch_id' => $testData['branch_id'],
                        'received_business_id' => $businessId,
                        'received_branch_id' => $branchId
                    ]);
                    return response()->json(['success' => false, 'message' => 'Business/Branch mismatch'], 400);
                }
            }

            // Create callback response record
            $callbackResponse = PaymentCallbackResponse::createFromMpesaCallback(
                $callbackData,
                $request,
                $testData['business_id'],
                $testData['branch_id']
            );

            // Update cache with callback response
            $testData['callback_response'] = $callbackResponse;
            Cache::put('mpesa_callback_' . $checkoutRequestId, $testData, 3600);

            // Update POS payment status
            $this->updatePosPaymentStatus($testData['ticket_id'], $callbackResponse);

            // Broadcast callback response to POS device
            $this->broadcastCallbackToPosDevice($testData['branch_id'], $callbackResponse);

            Log::info('POS M-PESA Callback Processed Successfully', [
                'checkout_request_id' => $checkoutRequestId,
                'ticket_id' => $testData['ticket_id'],
                'result_code' => $callbackResponse->result_code
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('POS M-PESA Callback Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response()->json(['success' => false, 'message' => 'Callback processing failed'], 500);
        }
    }

    /**
     * Update POS payment status based on M-PESA callback
     */
    private function updatePosPaymentStatus($ticketId, $callbackResponse)
    {
        try {
            $ticket = PosTicket::find($ticketId);
            if (!$ticket) {
                Log::error('POS M-PESA: Ticket not found', ['ticket_id' => $ticketId]);
                return;
            }

            // Find the pending M-PESA payment
            $payment = PosTicketPayment::where('ticket_id', $ticketId)
                ->where('method', 'mpesa')
                ->where('status', 'pending')
                ->first();

            if (!$payment) {
                Log::error('POS M-PESA: Pending payment not found', ['ticket_id' => $ticketId]);
                return;
            }

            // Update payment status based on M-PESA result
            if ($callbackResponse->result_code == 0) {
                // Success
                $payment->update([
                    'status' => 'completed',
                    'meta' => array_merge($payment->meta ?? [], [
                        'mpesa_receipt_number' => $callbackResponse->mpesa_receipt_number,
                        'transaction_date' => $callbackResponse->transaction_date,
                        'callback_response_id' => $callbackResponse->id
                    ])
                ]);

                // Update ticket totals
                $this->updateTicketTotals($ticket);

                Log::info('POS M-PESA Payment Completed', [
                    'ticket_id' => $ticketId,
                    'receipt_number' => $callbackResponse->mpesa_receipt_number
                ]);

            } else {
                // Failed
                $payment->update([
                    'status' => 'failed',
                    'meta' => array_merge($payment->meta ?? [], [
                        'error_code' => $callbackResponse->result_code,
                        'error_message' => $callbackResponse->result_desc,
                        'callback_response_id' => $callbackResponse->id
                    ])
                ]);

                Log::info('POS M-PESA Payment Failed', [
                    'ticket_id' => $ticketId,
                    'error_code' => $callbackResponse->result_code,
                    'error_message' => $callbackResponse->result_desc
                ]);
            }

        } catch (\Exception $e) {
            Log::error('POS M-PESA: Error updating payment status', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update ticket totals after successful payment
     */
    private function updateTicketTotals($ticket)
    {
        try {
            $completedPayments = PosTicketPayment::where('ticket_id', $ticket->id)
                ->where('status', 'completed')
                ->get();

            $totalPaid = $completedPayments->sum('amount');
            $amountDue = $ticket->total_amount - $totalPaid;

            $ticket->update([
                'amount_paid' => $totalPaid,
                'amount_due' => max(0, $amountDue),
                'status' => $amountDue <= 0 ? 'completed' : 'active'
            ]);

        } catch (\Exception $e) {
            Log::error('POS M-PESA: Error updating ticket totals', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get M-PESA credentials for a branch
     */
    public function getCredentials(Request $request)
    {
        try {
            // Get device information from request header (same as check-device endpoint)
            $device_uuid = $request->header('X-Device-UUID');
            
            Log::info('POS M-PESA Get Credentials: Device UUID from header', [
                'device_uuid' => $device_uuid,
                'headers' => $request->headers->all()
            ]);
            
            if (!$device_uuid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device UUID not provided in header.'
                ], 400);
            }
            
            $device = \App\Models\PosDevice::where('device_uuid', $device_uuid)->first();
            
            Log::info('POS M-PESA Get Credentials: Device lookup result', [
                'device_uuid' => $device_uuid,
                'device_found' => $device ? true : false,
                'device_id' => $device ? $device->id : null,
                'branch_id' => $device ? $device->branch_id : null
            ]);
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found or not registered.'
                ], 400);
            }
            
            $credentials = BranchMpesaCredential::where('branch_id', $device->branch_id)
                ->where('environment', 'sandbox') // Only sandbox for now
                ->where('is_active', true)
                ->first();

            if (!$credentials) {
                return response()->json([
                    'success' => false,
                    'message' => 'M-PESA credentials not configured for this branch'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'credentials' => [
                    'business_shortcode' => $credentials->business_shortcode,
                    'environment' => $credentials->environment,
                    'is_configured' => true
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('POS M-PESA: Error getting credentials', [
                'branch_id' => $request->branch_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get M-PESA credentials'
            ], 500);
        }
    }

    public function testSession(Request $request)
    {
        return response()->json([
            'session_device_uuid' => session('device_uuid'),
            'header_device_uuid' => $request->header('X-Device-UUID'),
            'user_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user_branch_id' => auth()->user() ? auth()->user()->branch_id : null,
            'all_headers' => $request->headers->all()
        ]);
    }

    /**
     * Get user-friendly error message for POS M-PESA errors
     */
    private function getPosMpesaErrorMessage($originalMessage, $errorCode = null)
    {
        // For POS users, keep messages simple and actionable
        if (str_contains($originalMessage, 'Failed to get access token')) {
            return 'M-PESA payment failed. Please contact your administrator to check M-PESA settings.';
        } elseif (str_contains($originalMessage, 'BusinessShortCode')) {
            return 'M-PESA payment failed. Please contact your administrator to check M-PESA settings.';
        } elseif (str_contains($originalMessage, 'PhoneNumber')) {
            return 'Invalid phone number. Please enter a valid Kenyan phone number.';
        } elseif (str_contains($originalMessage, 'Access denied (403)')) {
            return 'M-PESA payment failed. Please contact your administrator.';
        } elseif (str_contains($originalMessage, 'Unauthorized (401)')) {
            return 'M-PESA payment failed. Please contact your administrator to check M-PESA settings.';
        } elseif (str_contains($originalMessage, 'system is busy')) {
            return 'M-PESA system is busy. Please try again in a few minutes.';
        } elseif (str_contains($originalMessage, 'M-PESA service error (HTTP 500)')) {
            return 'M-PESA service is experiencing issues. Please try again later.';
        } elseif (str_contains($originalMessage, 'timeout')) {
            return 'Request timed out. Please check your connection and try again.';
        } elseif (str_contains($originalMessage, 'cURL error')) {
            return 'Network connection error. Please check your internet connection.';
        } else {
            return 'M-PESA payment failed. Please try again.';
        }
    }

    /**
     * Get error type for POS M-PESA errors
     */
    private function getPosMpesaErrorType($originalMessage)
    {
        if (str_contains($originalMessage, 'Failed to get access token')) {
            return 'authentication_failed';
        } elseif (str_contains($originalMessage, 'BusinessShortCode')) {
            return 'invalid_shortcode';
        } elseif (str_contains($originalMessage, 'PhoneNumber')) {
            return 'invalid_phone';
        } elseif (str_contains($originalMessage, 'Access denied (403)')) {
            return 'http_403';
        } elseif (str_contains($originalMessage, 'Unauthorized (401)')) {
            return 'http_401';
        } elseif (str_contains($originalMessage, 'system is busy')) {
            return 'system_busy';
        } elseif (str_contains($originalMessage, 'M-PESA service error (HTTP 500)')) {
            return 'http_500';
        } elseif (str_contains($originalMessage, 'timeout')) {
            return 'timeout';
        } elseif (str_contains($originalMessage, 'cURL error')) {
            return 'network_error';
        } else {
            return 'unknown_error';
        }
    }

    /**
     * Get user-friendly message for M-PESA callback result codes
     */
    private function getPosMpesaCallbackMessage($resultCode, $resultDesc, $phone, $amount)
    {
        $errorMessages = [
            '0' => "Payment successful! Amount: KES {$amount}, Phone: {$phone}",
            '1037' => "Payment failed - User not reachable. Please try again.",
            '1025' => "Payment failed - System error. Please try again.",
            '9999' => "Payment failed - System error. Please try again.",
            '1032' => "Payment cancelled by user.",
            '1' => "Payment failed - Insufficient balance.",
            '2001' => "Payment failed - Invalid PIN entered.",
            '1019' => "Payment failed - Transaction expired. Please try again.",
            '1001' => "Payment failed - Transaction in progress. Please wait 2-3 minutes before trying again."
        ];

        return $errorMessages[$resultCode] ?? "Payment failed: {$resultDesc}";
    }

    /**
     * Get status for M-PESA callback result codes
     */
    private function getPosMpesaCallbackStatus($resultCode)
    {
        if ($resultCode === '0') {
            return 'success';
        } elseif ($resultCode === '1032') {
            return 'cancelled';
        } elseif (in_array($resultCode, ['1037', '1025', '9999', '1', '2001', '1019', '1001'])) {
            return 'failed';
        } else {
            return 'pending';
        }
    }

    /**
     * Broadcast callback response to POS device
     */
    private function broadcastCallbackToPosDevice($branchId, $callbackResponse)
    {
        try {
            // Create a cache key for POS device updates
            $cacheKey = "pos_mpesa_callback_branch_{$branchId}";
            
            // Store the latest callback response for this branch
            $callbackData = [
                'result_code' => $callbackResponse->result_code,
                'result_desc' => $callbackResponse->result_desc,
                'amount' => $callbackResponse->amount,
                'phone_number' => $callbackResponse->phone_number,
                'mpesa_receipt_number' => $callbackResponse->mpesa_receipt_number,
                'transaction_date' => $callbackResponse->transaction_date,
                'balance' => $callbackResponse->balance,
                'checkout_request_id' => $callbackResponse->checkout_request_id,
                'merchant_request_id' => $callbackResponse->merchant_request_id,
                'status' => $callbackResponse->status,
                'created_at' => $callbackResponse->created_at->format('Y-m-d H:i:s'),
                'timestamp' => now()->toISOString()
            ];
            
            Cache::put($cacheKey, $callbackData, 300); // Cache for 5 minutes
            
            Log::info('POS M-PESA Callback broadcasted to branch', [
                'branch_id' => $branchId,
                'result_code' => $callbackResponse->result_code,
                'cache_key' => $cacheKey
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to broadcast callback to POS device', [
                'branch_id' => $branchId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Ensure callback URL exists for POS M-PESA payments
     */
    private function ensurePosCallbackUrlExists($credentials)
    {
        try {
            // Check if callback URL already exists for this branch and environment
            $existingCallback = \App\Models\PaymentCallbackUrl::where('branch_id', $credentials->branch_id)
                ->where('payment_type', 'mpesa')
                ->where('provider', 'safaricom')
                ->where('environment', $credentials->environment)
                ->first();

            if (!$existingCallback) {
                // Create callback URL for POS M-PESA payments with business and branch IDs
                $baseUrl = config('app.url');
                $businessId = $credentials->branch->business_id ?? null;
                $branchId = $credentials->branch_id;
                $callbackUrl = $baseUrl . "/api/mpesa/pos/mpesa/callback/business-{$businessId}-branch-{$branchId}";
                
                \App\Models\PaymentCallbackUrl::create([
                    'branch_id' => $credentials->branch_id,
                    'business_id' => $credentials->branch->business_id ?? null,
                    'payment_type' => 'mpesa_pos', // Different payment type for POS
                    'provider' => 'safaricom',
                    'environment' => $credentials->environment,
                    'callback_url' => $callbackUrl,
                    'description' => 'POS M-PESA Callback URL',
                    'is_verified' => false,
                    'is_active' => true
                ]);
                
            }
        } catch (\Exception $e) {

        }
    }
} 