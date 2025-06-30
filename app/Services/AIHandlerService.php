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

        // If intent is general/unclear, clarify
        if ($intent === 'general') {
            return 'Did you mean profit, total revenue, or something else?';
        }

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

        switch ($intent) {
            case 'compare_weekly_profit':
                return [
                    'branches' => $business->branches()->get(['id', 'name'])->toArray(),
                    'profit_this_week' => $business->calculateProfitForPeriod(now()->startOfWeek(), now()->endOfWeek()),
                    'profit_last_week' => $business->calculateProfitForPeriod(now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()),
                ];
            case 'out_of_stock_sms':
                $products = $business->products()
                    ->with(['sales' => function($q) {
                        $q->where('created_at', '>=', now()->subDay());
                    }, 'supplier'])
                    ->where('stock', 0)
                    ->whereHas('sales', function($q) {
                        $q->where('created_at', '>=', now()->subDay())
                          ->groupBy('product_id')
                          ->havingRaw('COUNT(*) > 5');
                    })->get(['id', 'name', 'supplier_id']);
                // Only include essential fields
                $productsArr = $products->map(function($p) {
                    return [
                        'name' => $p->name,
                        'supplier' => $p->supplier ? $p->supplier->name : null,
                        'recent_sales' => $p->sales->count()
                    ];
                });
                return ['products' => $productsArr];
            case 'sellers_by_category':
                $sellers = $business->sellers()
                    ->with(['sales.product.category'])
                    ->withCount(['sales as category_count' => function($q) {
                        $q->select(\DB::raw('COUNT(DISTINCT category_id)'));
                    }])->having('category_count', '>', 3)->get(['id', 'name']);
                $sellersArr = $sellers->map(function($s) {
                    return [
                        'name' => $s->name,
                        'category_count' => $s->category_count
                    ];
                });
                return ['sellers' => $sellersArr];
            case 'todays_sales':
                $sales = $business->sales()
                    ->with(['branch', 'items.product'])
                    ->whereDate('created_at', now()->toDateString())
                    ->get();
                $salesArr = $sales->map(function($sale) {
                    return [
                        'total' => $sale->total,
                        'time' => $sale->created_at->toDateTimeString(),
                        'branch' => $sale->branch ? $sale->branch->name : null,
                        'items' => $sale->items->map(function($item) {
                            return [
                                'product' => $item->product ? $item->product->name : null,
                                'qty' => $item->quantity,
                                'price' => $item->price
                            ];
                        })
                    ];
                });
                return ['sales' => $salesArr];
            case 'top_sales_sw':
                $products = $business->products()
                    ->with(['sales' => function($q) {
                        $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    }])
                    ->where('discount', '<', 10)
                    ->orderByDesc('sales_count')
                    ->take(5)
                    ->get(['id', 'name', 'sales_count']);
                $productsArr = $products->map(function($p) {
                    return [
                        'name' => $p->name,
                        'sales_count' => $p->sales_count
                    ];
                });
                return ['products' => $productsArr];
            default:
                return ['business' => $business->only(['id', 'name'])];
        }
    }

    protected function formatPrompt($question, $context)
    {
        return "Business data: " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\nUser question: $question";
    }
} 