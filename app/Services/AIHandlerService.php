<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIHandlerService
{
    public function handle($user, $question)
    {
        // Quick check if Ollama is available
        if (!$this->isOllamaAvailable()) {
            return 'We will be back soon.';
        }
        
        $intent = $this->classifyIntent($question);
        $context = $this->gatherContext($user, $intent, $question);

        // Always process the question with data context, no ambiguous fallback

        $prompt = $this->formatPrompt($question, $context);
        // Limit token size for LLaMA
        if (strlen($prompt) > 3800) {
            $prompt = substr($prompt, 0, 3800) . "\n\n[Data truncated to fit limit]";
        }

        $endpoint = config('ai.endpoint');
        $model = config('ai.model');
        $ollamaResponse = null;
        $answer = null;
        try {
            // Reduced timeout to 10 seconds for faster response
            $ollamaResponse = Http::timeout(10)->post($endpoint, [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false
            ]);
            $response = $ollamaResponse->json();
            if (!isset($response['response']) || !$response['response']) {
                Log::error('Ollama returned unexpected response', ['response' => $response]);
                $answer = 'We will be back soon.';
            } else {
                $answer = $response['response'];
            }
        } catch (\Exception $e) {
            Log::error('Ollama call failed', ['error' => $e->getMessage()]);
            $answer = 'We will be back soon.';
        }

        Log::info('AI Prompt Sent', [
            'user_id' => $user->id,
            'intent' => $intent,
            'question' => $question,
            'prompt' => $prompt,
            'response' => $answer
        ]);
        return $answer;
    }

    // Check if Ollama is available
    protected function isOllamaAvailable()
    {
        try {
            $response = Http::timeout(3)->get('http://localhost:11434/api/tags');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    // Chat method for handling conversation context
    public function chat($user, $context)
    {
        try {
            // Get the last user message from context
            $lastUserMessage = null;
            foreach (array_reverse($context) as $msg) {
                if ($msg['role'] === 'user') {
                    $lastUserMessage = $msg['content'];
                    break;
                }
            }
            
            if (!$lastUserMessage) {
                return "I'm sorry, I couldn't understand your message. Could you please rephrase it?";
            }
            
            // Use the existing handle method with the last user message
            return $this->handle($user, $lastUserMessage);
            
        } catch (\Exception $e) {
            Log::error('AI Chat error: ' . $e->getMessage());
            return "I'm sorry, I'm having trouble processing your request right now. Please try again later.";
        }
    }

    protected function classifyIntent($question)
    {
        $cacheKey = 'ai_intent_' . md5($question);
        return cache()->remember($cacheKey, 6000, function () use ($question) {
            // 1. Try LLaMA intent classification
            $llamaPrompt = "Classify the following question into one of these intents: todays_sales, compare_weekly_profit, out_of_stock_sms, sellers_by_category, top_sales_sw, general.\n\nQuestion: $question\n\nIntent:";
            try {
                $llamaResponse = Http::timeout(5)->post(config('ai.endpoint'), [
                    'model' => config('ai.model'),
                    'prompt' => $llamaPrompt,
                    'stream' => false
                ]);
                $intent = strtolower(trim($llamaResponse->json()['response'] ?? ''));
                $validIntents = [
                    'todays_sales', 'compare_weekly_profit', 'out_of_stock_sms', 'sellers_by_category', 'top_sales_sw', 'general'
                ];
                if (in_array($intent, $validIntents)) {
                    return $intent;
                }
            } catch (\Exception $e) {
                // Fallback to regex below
            }
            // 2. Fallback: regex-based
            $q = strtolower($question);
            if (preg_match('/(compare|difference).*profit.*week/i', $q)) return 'compare_weekly_profit';
            if (preg_match('/out of stock|sold more than/i', $q)) return 'out_of_stock_sms';
            if (preg_match('/category.*seller/i', $q)) return 'sellers_by_category';
            if (preg_match('/sales.*today/i', $q)) return 'todays_sales';
            if (preg_match('/mauzo makubwa/i', $q)) return 'top_sales_sw';
            // ...add more
            return 'general';
        });
    }

    protected function gatherContext($user, $intent, $question)
    {
        // Access guard: support both owner and seller
        $business = null;
        if (method_exists($user, 'hasRole') && $user->hasRole('seller')) {
            $branch = $user->branch;
            if (!$branch) return [];
            $business = $branch->business;
        } else {
            $business = $user->business;
        }
        if (!$business) return [];

        // Gather today's business data from all relevant tables
        $today = now()->toDateString();
        $context = [];
        // Sales
        $context['sales'] = $business->sales()->whereDate('created_at', $today)->sum('total_amount');
        // Purchases
        $context['purchases'] = $business->purchases()->whereDate('created_at', $today)->sum('total_amount');
        // Expenses
        $context['expenses'] = $business->transactions()->where('type', 'debit')->whereDate('created_at', $today)->sum('amount');
        // Profit
        $context['profit'] = $context['sales'] - $context['purchases'] - $context['expenses'];
        // Products sold today
        $context['products_sold'] = $business->sales()->whereDate('created_at', $today)->with('items.product')->get()->flatMap(function($sale) {
            return $sale->items->map(function($item) {
                return [
                    'product' => $item->product ? $item->product->name : null,
                    'qty' => $item->quantity,
                    'price' => $item->price
                ];
            });
        })->toArray();
        // Out of stock products
        $context['out_of_stock'] = $business->products()->where('stock', 0)->pluck('name')->toArray();
        // Top sellers
        $context['top_sellers'] = $business->sellers()->withCount(['sales' => function($q) use ($today) {
            $q->whereDate('created_at', $today);
        }])->orderByDesc('sales_count')->take(5)->get(['id', 'name', 'sales_count'])->toArray();
        // Add more tables as needed (e.g., payments, discounts, etc.)
        $context['payments'] = $business->payments()->whereDate('created_at', $today)->sum('amount');
        $context['discounts'] = $business->discounts()->whereDate('created_at', $today)->sum('amount');
        $context['invoices'] = $business->invoices()->whereDate('created_at', $today)->sum('total');
        $context['suppliers'] = $business->suppliers()->count();
        $context['customers'] = $business->customers()->count();
        // You can add more as needed for your business logic
        return $context;
    }

    protected function formatPrompt($question, $context)
    {
        return "Business data: " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\nUser question: $question";
    }
} 