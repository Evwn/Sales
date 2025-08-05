<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Branch;
use App\Models\BranchMpesaCredential;
use App\Models\PaymentCallbackUrl;
use App\Models\PaymentCallbackResponse;
use App\Services\MpesaService;
use App\Helpers\PhoneHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentTypesController extends Controller
{
    /**
     * Get the default callback URL using APP_URL
     */
    private function getDefaultCallbackUrl()
    {
        $baseUrl = config('app.url');
        return $baseUrl . '/api/mpesa/callback';
    }

    public function index()
    {
        $user = auth()->user();
        
        // Get all businesses the user has access to
        $businesses = Business::whereHas('branches.users', function($query) use ($user) {
            $query->where('id', $user->id);
        })->orWhereHas('users', function($query) use ($user) {
            $query->where('id', $user->id);
        })->get();
        
        // Get current business and branch
        $currentBusiness = null;
        $currentBranch = null;
        
        if ($user->business_id) {
            $currentBusiness = Business::find($user->business_id);
        }
        
        if ($user->branch_id) {
            $currentBranch = Branch::find($user->branch_id);
        }
        
        // Get M-PESA credentials for current branch
        $mpesaCredentials = null;
        if ($user->branch_id) {
            $mpesaCredentials = BranchMpesaCredential::where('branch_id', $user->branch_id)
                ->where('is_active', true)
                ->first();
                
            // Auto-create callback URL if credentials exist but no callback URL
            if ($mpesaCredentials) {
                $this->ensureCallbackUrlExists($mpesaCredentials);
            }
        }
        
        return inertia('settings/PaymentTypes', [
            'businesses' => $businesses,
            'currentBusiness' => $currentBusiness,
            'currentBranch' => $currentBranch,
            'mpesaCredentials' => $mpesaCredentials,
            'user' => $user,
        ]);
    }

    public function getBranches(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id'
        ]);
        
        $branches = Branch::where('business_id', $request->business_id)
            ->where('status', 'active')
            ->get();
            
        return response()->json([
            'success' => true,
            'branches' => $branches
        ]);
    }

    public function selectBusiness(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id'
        ]);
        
        $user = auth()->user();
        $user->business_id = $request->business_id;
        $user->branch_id = null; // Reset branch when business changes
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Business selected successfully'
        ]);
    }

        public function selectBranch(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id'
        ]);

        $user = auth()->user();
        $user->branch_id = $request->branch_id;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Branch selected successfully'
        ]);
    }

    public function getCredentials(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'environment' => 'required|in:sandbox,live'
        ]);

        try {
            $credentials = BranchMpesaCredential::where('branch_id', $request->branch_id)
                ->where('environment', $request->environment)
                ->where('is_active', true)
                ->first();

            if ($credentials) {
                return response()->json([
                    'success' => true,
                    'credentials' => [
                        'environment' => $credentials->environment,
                        'consumer_key' => $credentials->consumer_key,
                        'consumer_secret' => $credentials->consumer_secret,
                        'business_shortcode' => $credentials->business_shortcode,
                        'passkey' => $credentials->passkey,
                        'description' => $credentials->description,
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'credentials' => null,
                    'message' => 'No credentials found for this branch and environment'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching M-PESA credentials', [
                'error' => $e->getMessage(),
                'branch_id' => $request->branch_id,
                'environment' => $request->environment,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch credentials: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testMpesaCredentials(Request $request)
    {
        $request->validate([
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
            'business_shortcode' => 'required|string',
            'passkey' => 'required|string',
            'environment' => 'required|in:sandbox,live',
            'test_phone' => 'nullable|string',
            'test_amount' => 'required|numeric|min:1|max:100000'
        ]);

        try {
            // Get the user's current branch for testing
            $user = auth()->user();
            $branch = null;
            
            if ($user->branch_id) {
                $branch = \App\Models\Branch::find($user->branch_id);
            }
            
            // Validate that user has a branch selected
            if (!$user->branch_id || !$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a branch before testing M-PESA credentials.',
                    'error_type' => 'no_branch_selected',
                    'details' => 'You need to select a branch to test M-PESA credentials.'
                ], 400);
            }
            
            // Create temporary credentials for testing
            $tempCredentials = new BranchMpesaCredential([
                'branch_id' => $user->branch_id ?? 1, // Use current branch or default
                'consumer_key' => $request->consumer_key,
                'consumer_secret' => $request->consumer_secret,
                'business_shortcode' => $request->business_shortcode,
                'passkey' => $request->passkey,
                'environment' => $request->environment,
            ]);
            
            // Set the branch relationship manually for testing
            if ($branch) {
                $tempCredentials->setRelation('branch', $branch);
            }
            
            // Test authentication
            $mpesaService = new MpesaService($tempCredentials);
            $accessToken = $mpesaService->getAccessToken();
            
            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication failed. Please check your Consumer Key and Consumer Secret.',
                    'error_type' => 'authentication_failed',
                    'details' => 'Unable to obtain access token from M-PESA API'
                ], 400);
            }
            
            // Validate and format phone number
            $testPhone = $request->test_phone ?: $user->phone ?: '254708374149'; // Use user's phone or default
            
            // Format phone number for M-PESA
            $formattedPhone = PhoneHelper::formatForMpesa($testPhone);
            
            // Validate phone number
            $phoneError = PhoneHelper::getValidationError($testPhone);
            if ($phoneError) {
                return response()->json([
                    'success' => false,
                    'message' => $phoneError
                ], 400);
            }

            // Skip ongoing transaction check to avoid multiple STK pushes
            // We'll rely on the callback response to determine the final status
            
            $testAmount = $request->test_amount;
            
            // Create transaction description with branch name
            $branchName = $branch ? $branch->name : 'Test Branch';
            $transactionDesc = "Test payment for {$branchName}";
            
            $result = $mpesaService->initiateStkPush($formattedPhone, $testAmount, $transactionDesc);
            
            if ($result['success']) {
                $checkoutRequestId = $result['checkout_request_id'];
                
                // Store test data for callback reference
                $testDataCacheKey = 'mpesa_test_data_' . $checkoutRequestId;
                \Cache::put($testDataCacheKey, [
                    'amount' => $testAmount,
                    'phone' => $formattedPhone,
                    'original_phone' => $testPhone,
                    'branch_name' => $branchName
                ], 300); // Cache for 5 minutes
                
                // Return success immediately after STK push initiation
                // The actual payment result will come via callback
                return response()->json([
                    'success' => true,
                    'message' => "STK push sent successfully! Amount: KES {$testAmount}, Phone: {$formattedPhone}, Branch: {$branchName}",
                    'status' => 'stk_sent',
                    'checkout_request_id' => $checkoutRequestId,
                    'merchant_request_id' => $result['merchant_request_id'],
                    'test_phone' => $formattedPhone,
                    'original_phone' => $testPhone,
                    'test_amount' => $testAmount,
                    'branch_name' => $branchName,
                    'note' => 'Please check your phone and complete the payment. The final result will be available via callback.'
                ]);
            } else {
                $errorMessage = $result['message'];
                $errorType = 'stk_push_failed';
                
                // Provide more specific error messages for STK push failures
                if (str_contains($errorMessage, 'Failed to get access token')) {
                    $errorMessage = 'STK push failed due to authentication issues. Please check your Consumer Key and Consumer Secret.';
                    $errorType = 'authentication_failed';
                } elseif (str_contains($errorMessage, 'BusinessShortCode')) {
                    $errorMessage = 'STK push failed due to invalid Business Shortcode. Please check your Business Shortcode.';
                    $errorType = 'invalid_shortcode';
                } elseif (str_contains($errorMessage, 'PhoneNumber')) {
                    $errorMessage = 'STK push failed due to invalid phone number format. Please check the phone number.';
                    $errorType = 'invalid_phone';
                } elseif (str_contains($errorMessage, 'Access denied (403)')) {
                    $errorMessage = 'Access denied by M-PESA API. Please check your credentials and account status.';
                    $errorType = 'http_403';
                } elseif (str_contains($errorMessage, 'Unauthorized (401)')) {
                    $errorMessage = 'Authentication failed. Please check your Consumer Key and Consumer Secret.';
                    $errorType = 'http_401';
                } elseif (str_contains($errorMessage, 'system is busy')) {
                    $errorMessage = 'M-PESA system is currently busy. Please try again in a few minutes.';
                    $errorType = 'system_busy';
                } elseif (str_contains($errorMessage, 'M-PESA service error (HTTP 500)')) {
                    $errorMessage = 'M-PESA service is experiencing issues. Please try again later.';
                    $errorType = 'http_500';
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error_type' => $errorType,
                    'original_error' => $result['message'],
                    'http_status' => $result['http_status'] ?? null,
                    'response_body' => $result['response_body'] ?? null
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('M-PESA credential test failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'Test failed due to an unexpected error.';
            $errorType = 'unexpected_error';
            
            // Provide more specific error messages for common exceptions
            if (str_contains($e->getMessage(), 'cURL error')) {
                $errorMessage = 'Network connection error. Please check your internet connection and try again.';
                $errorType = 'network_error';
            } elseif (str_contains($e->getMessage(), 'SSL certificate')) {
                $errorMessage = 'SSL certificate verification failed. Please check your system\'s SSL configuration.';
                $errorType = 'ssl_error';
            } elseif (str_contains($e->getMessage(), 'timeout')) {
                $errorMessage = 'Request timed out. Please check your internet connection and try again.';
                $errorType = 'timeout';
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error_type' => $errorType,
                'original_error' => $e->getMessage(),
                'details' => 'Please contact support if this error persists.'
            ], 500);
        }
    }

    /**
     * Get user-friendly error message for M-PESA error codes
     */
    private function getMpesaErrorMessage($resultCode, $resultDesc, $phone, $amount)
    {
        $errorMessages = [
            '1037' => "DS timeout - user cannot be reached. Phone: {$phone}, Amount: KES {$amount}.<br><br><strong>Possible causes:</strong><br>• SIM card needs update (dial *234*1*6#)<br>• SIM card is too old (3+ years)<br>• Mobile phone is offline<br><br><strong>Solution:</strong><br>• Update SIM card or perform SIM swap at Safaricom Agent shop<br>• Ensure mobile phone is online",
            
            '1025' => "An error occurred while sending a push request. Phone: {$phone}, Amount: KES {$amount}.<br><br><strong>Possible causes:</strong><br>• System error<br>• USSD message too long (more than 182 characters)<br><br><strong>Solution:</strong><br>• Retry the request<br>• Ensure system is working correctly<br>• Keep messaging under 182 characters",
            
            '9999' => "An error occurred while sending a push request. Phone: {$phone}, Amount: KES {$amount}.<br><br><strong>Solution:</strong><br>• Retry the request<br>• Check system configuration",
            
            '1037' => "No response from the user. Phone: {$phone}, Amount: KES {$amount}.<br><br><strong>Cause:</strong><br>STK Push Prompt reached customer but response was not sent back on time.<br><br><strong>Solution:</strong><br>• Retry after receiving callback<br>• Notify user that request failed",
            
            '1032' => "The request was canceled by the user. Phone: {$phone}, Amount: KES {$amount}.<br><br><strong>Possible causes:</strong><br>• STK Prompt timed out (1-3 minutes)<br>• User canceled request on phone<br><br><strong>Solution:</strong><br>• Inform user they did not respond in time<br>• Try again",
            
            '1' => "Insufficient balance for the transaction. Phone: {$phone}, Amount: KES {$amount}.<br><br><strong>Possible causes:</strong><br>• Subscriber declined using Overdraft (Fuliza)<br>• Insufficient funds on M-PESA<br><br><strong>Solution:</strong><br>• Deposit funds on M-PESA<br>• Use Overdraft (Fuliza) when prompted",
            
            '2001' => "Invalid initiator information. Phone: {$phone}, Amount: KES {$amount}.<br><br><strong>Possible causes:</strong><br>• Invalid password input<br>• Wrong M-PESA PIN entered<br><br><strong>Solution:</strong><br>• Use correct user credentials<br>• Enter correct M-PESA PIN",
            
            '1019' => "Transaction has expired. Phone: {$phone}, Amount: KES {$amount}.<br><br><strong>Cause:</strong><br>Transaction took too long to process within allowable time.<br><br><strong>Solution:</strong><br>• Initiate a new transaction",
            
            '1001' => "Unable to lock subscriber - transaction already in process. Phone: {$phone}, Amount: KES {$amount}.<br><br><strong>Possible causes:</strong><br>• Duplicated MSISDN<br>• Existing USSD session<br>• Conflicting sessions<br>• Ongoing USSD Session<br>• Supplementary services barred<br><br><strong>Solution:</strong><br>• Close existing session<br>• Wait 2-3 minutes before retrying<br>• Send one push request at a time<br>• Contact Safaricom for unbarring"
        ];

        return $errorMessages[$resultCode] ?? "Payment failed: {$resultDesc}. Phone: {$phone}, Amount: KES {$amount}";
    }

    /**
     * Get error type for M-PESA error codes
     */
    private function getMpesaErrorType($resultCode)
    {
        $errorTypes = [
            '1037' => 'timeout_user_unreachable',
            '1025' => 'system_error',
            '9999' => 'system_error',
            '1032' => 'user_cancelled',
            '1' => 'insufficient_balance',
            '2001' => 'invalid_credentials',
            '1019' => 'transaction_expired',
            '1001' => 'subscriber_locked'
        ];

        return $errorTypes[$resultCode] ?? 'unknown_error';
    }

    /**
     * Ensure callback URL exists for M-PESA credentials
     */
    private function ensureCallbackUrlExists($credentials)
    {
        try {
            // Check if callback URL already exists
            $existingCallback = PaymentCallbackUrl::where('branch_id', $credentials->branch_id)
                ->where('payment_type', 'mpesa')
                ->where('provider', 'safaricom')
                ->where('environment', $credentials->environment)
                ->first();

            if (!$existingCallback) {
                // Create callback URL automatically using APP_URL
                $defaultCallbackUrl = $this->getDefaultCallbackUrl();
                $credentials->createCallbackUrl($defaultCallbackUrl);
                
                Log::info('Auto-created callback URL for M-PESA credentials', [
                    'branch_id' => $credentials->branch_id,
                    'environment' => $credentials->environment,
                    'callback_url' => $defaultCallbackUrl
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to ensure callback URL exists', [
                'branch_id' => $credentials->branch_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check for ongoing M-PESA transactions
     */
    public function checkOngoingTransaction(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'environment' => 'required|in:sandbox,live'
        ]);

        try {
            $user = auth()->user();
            
            if (!$user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a branch first'
                ], 400);
            }

            // Get credentials for the branch and environment
            $credentials = BranchMpesaCredential::where('branch_id', $user->branch_id)
                ->where('environment', $request->environment)
                ->where('is_active', true)
                ->first();

            if (!$credentials) {
                return response()->json([
                    'success' => false,
                    'message' => 'No M-PESA credentials found for this branch and environment'
                ], 400);
            }

            // Format phone number
            $formattedPhone = PhoneHelper::formatForMpesa($request->phone);
            
            // Check for ongoing transaction
            $mpesaService = new MpesaService($credentials);
            $result = $mpesaService->checkOngoingTransaction($formattedPhone);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Failed to check ongoing transaction', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check ongoing transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveMpesaCredentials(Request $request)
    {
        $request->validate([
            'environment' => 'required|in:sandbox,live',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
            'business_shortcode' => 'required|string',
            'passkey' => 'required|string',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $user = auth()->user();
            
            if (!$user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a branch first'
                ], 400);
            }
            
            // Save or update credentials
            $credentials = BranchMpesaCredential::updateOrCreate(
                [
                    'branch_id' => $user->branch_id,
                    'environment' => $request->environment
                ],
                [
                    'consumer_key' => $request->consumer_key,
                    'consumer_secret' => $request->consumer_secret,
                    'business_shortcode' => $request->business_shortcode,
                    'passkey' => $request->passkey,
                    'description' => $request->description,
                    'is_active' => true,
                    'is_testing' => $request->environment === 'sandbox'
                ]
            );

            // Create or update callback URL
            $credentials->createCallbackUrl();
            
            return response()->json([
                'success' => true,
                'message' => 'M-PESA credentials saved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to save M-PESA credentials', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save credentials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get callback URLs for the current branch with environment filtering
     */
    public function getCallbackUrls(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a branch first'
                ], 400);
            }

            // Build query with environment filtering
            $query = PaymentCallbackUrl::where(function($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhere('business_id', $user->business_id);
            });

            // Filter by environment if provided
            if ($request->has('environment') && in_array($request->environment, ['sandbox', 'live'])) {
                $query->where('environment', $request->environment);
            }

            // Filter by payment type if provided
            if ($request->has('payment_type') && !empty($request->payment_type)) {
                $query->where('payment_type', $request->payment_type);
            }

            $callbackUrls = $query->get()
                ->map(function ($callback) {
                    return $callback->getFormattedInfo();
                });

            // Group by environment for better organization
            $groupedUrls = [
                'sandbox' => $callbackUrls->where('environment', 'sandbox')->values(),
                'live' => $callbackUrls->where('environment', 'live')->values(),
                'all' => $callbackUrls
            ];

            return response()->json([
                'success' => true,
                'callback_urls' => $groupedUrls,
                'filters' => [
                    'environment' => $request->environment ?? 'all',
                    'payment_type' => $request->payment_type ?? 'all'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get callback URLs', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get callback URLs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update callback URL
     */
    public function updateCallbackUrl(Request $request)
    {
        $request->validate([
            'callback_url_id' => 'required|exists:payment_callback_urls,id',
            'callback_url' => 'required|url',
            'description' => 'nullable|string'
        ]);

        try {
            $user = auth()->user();
            
            $callbackUrl = PaymentCallbackUrl::findOrFail($request->callback_url_id);
            
            // Check if user has access to this callback URL
            if ($callbackUrl->branch_id && $callbackUrl->branch_id !== $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $callbackUrl->update([
                'callback_url' => $request->callback_url,
                'description' => $request->description,
                'is_verified' => false, // Reset verification when URL changes
                'verified_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Callback URL updated successfully',
                'callback_url' => $callbackUrl->getFormattedInfo()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update callback URL', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update callback URL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get callback responses for the current branch with environment filtering
     */
    public function getCallbackResponses(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a branch first'
                ], 400);
            }

            // Build query with environment filtering
            $query = PaymentCallbackResponse::where(function($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhere('business_id', $user->business_id);
            });

            // Filter by environment if provided
            if ($request->has('environment') && in_array($request->environment, ['sandbox', 'live'])) {
                $query->where('environment', $request->environment);
            }

            // Filter by payment type if provided
            if ($request->has('payment_type') && !empty($request->payment_type)) {
                $query->where('payment_type', $request->payment_type);
            }

            // Filter by status if provided
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            $responses = $query->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($response) {
                    return $response->getFormattedInfo();
                });

            // Group by environment for better organization
            $groupedResponses = [
                'sandbox' => $responses->where('environment', 'sandbox')->values(),
                'live' => $responses->where('environment', 'live')->values(),
                'all' => $responses
            ];

            return response()->json([
                'success' => true,
                'callback_responses' => $groupedResponses,
                'filters' => [
                    'environment' => $request->environment ?? 'all',
                    'payment_type' => $request->payment_type ?? 'all',
                    'status' => $request->status ?? 'all'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get callback responses', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get callback responses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Query payment status manually
     */
    public function queryPaymentStatus(Request $request)
    {
        $request->validate([
            'checkout_request_id' => 'required|string'
        ]);

        try {
            $user = auth()->user();
            
            if (!$user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a branch first'
                ], 400);
            }

            // First check cache for immediate callback response
            $cacheKey = 'mpesa_callback_' . $request->checkout_request_id;
            $cachedCallback = \Cache::get($cacheKey);
            
            if ($cachedCallback) {
                // Log that we found the cached callback response
                Log::info('Cached callback response found for polling', [
                    'checkout_request_id' => $request->checkout_request_id,
                    'result_code' => $cachedCallback['result_code'],
                    'result_desc' => $cachedCallback['result_desc'],
                    'user_id' => auth()->id(),
                    'source' => 'cache'
                ]);
                
                // Return the cached callback response
                return response()->json([
                    'success' => true,
                    'message' => 'Callback response found (cached)',
                    'callback_response' => $cachedCallback
                ]);
            }
            
            // Check database for callback response
            $existingCallback = PaymentCallbackResponse::where('checkout_request_id', $request->checkout_request_id)
                ->where('branch_id', $user->branch_id)
                ->first();

            if ($existingCallback) {
                // Log that we found the callback response
                Log::info('Callback response found for polling', [
                    'checkout_request_id' => $request->checkout_request_id,
                    'result_code' => $existingCallback->result_code,
                    'result_desc' => $existingCallback->result_desc,
                    'user_id' => auth()->id(),
                    'source' => 'database'
                ]);
                
                // Return the existing callback response
                return response()->json([
                    'success' => true,
                    'message' => 'Callback response found',
                    'callback_response' => [
                        'result_code' => $existingCallback->result_code,
                        'result_desc' => $existingCallback->result_desc,
                        'amount' => $existingCallback->amount,
                        'phone_number' => $existingCallback->phone_number,
                        'mpesa_receipt_number' => $existingCallback->mpesa_receipt_number,
                        'transaction_date' => $existingCallback->transaction_date,
                        'balance' => $existingCallback->balance,
                        'checkout_request_id' => $existingCallback->checkout_request_id,
                        'merchant_request_id' => $existingCallback->merchant_request_id,
                        'status' => $existingCallback->status,
                        'created_at' => $existingCallback->created_at->format('Y-m-d H:i:s'),
                        'is_processed' => $existingCallback->is_processed
                    ]
                ]);
            }

            // If no callback response exists, return success but with no callback data
            // This allows the frontend to continue polling
            return response()->json([
                'success' => true,
                'message' => 'No callback response found yet, continue polling',
                'callback_response' => null
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to query payment status', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'checkout_request_id' => $request->checkout_request_id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to query payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear callback cache after frontend receives response
     */
    public function clearCallbackCache(Request $request)
    {
        $request->validate([
            'checkout_request_id' => 'required|string'
        ]);

        try {
            $cacheKey = 'mpesa_callback_' . $request->checkout_request_id;
            \Cache::forget($cacheKey);
            
            Log::info('Callback cache cleared', [
                'checkout_request_id' => $request->checkout_request_id,
                'cache_key' => $cacheKey,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to clear callback cache', [
                'error' => $e->getMessage(),
                'checkout_request_id' => $request->checkout_request_id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine status from M-PESA result code
     */
    private function determineStatusFromResultCode($resultCode)
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

    public function callback(Request $request)
    {
        // Log the callback data for debugging
        Log::info('M-PESA Callback received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        try {
            // Parse M-PESA callback data
            $callbackData = $request->all();
            
            // Save the callback response to database
            $callbackResponse = PaymentCallbackResponse::createFromMpesaCallback($callbackData, $request);
            
            if ($callbackResponse) {
                // Update the callback URL's last_callback_at timestamp
                $callbackUrl = PaymentCallbackUrl::where('callback_url', $request->url())->first();
                if ($callbackUrl) {
                    $callbackUrl->markCallbackReceived();
                }
                
                            // Store the callback response in cache for immediate frontend access
            $cacheKey = 'mpesa_callback_' . $callbackResponse->checkout_request_id;
            
            // Get the original test data from the STK push request
            $originalTestData = \Cache::get('mpesa_test_data_' . $callbackResponse->checkout_request_id);
            
            $callbackData = [
                'result_code' => $callbackResponse->result_code,
                'result_desc' => $callbackResponse->result_desc,
                'amount' => $callbackResponse->amount ?: ($originalTestData['amount'] ?? null),
                'phone_number' => $callbackResponse->phone_number ?: ($originalTestData['phone'] ?? null),
                'mpesa_receipt_number' => $callbackResponse->mpesa_receipt_number,
                'transaction_date' => $callbackResponse->transaction_date,
                'balance' => $callbackResponse->balance,
                'checkout_request_id' => $callbackResponse->checkout_request_id,
                'merchant_request_id' => $callbackResponse->merchant_request_id,
                'status' => $callbackResponse->status,
                'created_at' => $callbackResponse->created_at->format('Y-m-d H:i:s'),
                'is_processed' => $callbackResponse->is_processed,
                'test_amount' => $originalTestData['amount'] ?? null,
                'test_phone' => $originalTestData['phone'] ?? null
            ];
                
                // Cache for 5 minutes to allow frontend to pick it up
                \Cache::put($cacheKey, $callbackData, 300);
                
                Log::info('M-PESA callback response saved successfully', [
                    'response_id' => $callbackResponse->id,
                    'status' => $callbackResponse->status,
                    'result_code' => $callbackResponse->result_code,
                    'cache_key' => $cacheKey,
                    'cached_data' => $callbackData
                ]);
            }
            
            // Check if this is a valid M-PESA callback
            if (isset($callbackData['Body']['stkCallback'])) {
                $stkCallback = $callbackData['Body']['stkCallback'];
                
                $merchantRequestId = $stkCallback['MerchantRequestID'] ?? 'N/A';
                $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? 'N/A';
                $resultCode = $stkCallback['ResultCode'] ?? 'N/A';
                $resultDesc = $stkCallback['ResultDesc'] ?? 'N/A';
                
                // Log structured callback data
                Log::info('M-PESA STK Callback parsed', [
                    'merchant_request_id' => $merchantRequestId,
                    'checkout_request_id' => $checkoutRequestId,
                    'result_code' => $resultCode,
                    'result_desc' => $resultDesc,
                    'callback_metadata' => $stkCallback['CallbackMetadata'] ?? null,
                    'timestamp' => now()->toISOString()
                ]);
                
                // If successful transaction, log additional details
                if ($resultCode === 0 && isset($stkCallback['CallbackMetadata']['Item'])) {
                    $metadata = [];
                    foreach ($stkCallback['CallbackMetadata']['Item'] as $item) {
                        $metadata[$item['Name']] = $item['Value'];
                    }
                    
                    Log::info('M-PESA Successful Transaction Details', [
                        'amount' => $metadata['Amount'] ?? 'N/A',
                        'mpesa_receipt_number' => $metadata['MpesaReceiptNumber'] ?? 'N/A',
                        'transaction_date' => $metadata['TransactionDate'] ?? 'N/A',
                        'phone_number' => $metadata['PhoneNumber'] ?? 'N/A',
                        'balance' => $metadata['Balance'] ?? 'N/A'
                    ]);
                }
                
                // Log error details if transaction failed
                if ($resultCode !== 0) {
                    $errorMessage = $this->getMpesaErrorMessage($resultCode, $resultDesc, 'N/A', 'N/A');
                    Log::warning('M-PESA Transaction Failed', [
                        'result_code' => $resultCode,
                        'result_desc' => $resultDesc,
                        'user_friendly_message' => strip_tags($errorMessage)
                    ]);
                }
            } else {
                Log::warning('Invalid M-PESA callback format received', [
                    'callback_data' => $callbackData
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error processing M-PESA callback', [
                'error' => $e->getMessage(),
                'callback_data' => $request->all()
            ]);
        }

        // Return success response to M-PESA (always return 200 to acknowledge receipt)
        return response()->json([
            'success' => true,
            'message' => 'Callback received and processed'
        ]);
    }
} 