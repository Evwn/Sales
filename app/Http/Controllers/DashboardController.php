<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Sale;
use App\Models\Business;
use App\Models\Branch;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\SalesReceiptItem;
use App\Models\SalesReceipt;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Debug information
        Log::info('User data:', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'business_id' => $user->business_id,
            'branch_id' => $user->branch_id
        ]);

        // Get business based on user role
        $business = null;
        if ($user->hasRole('owner')) {
            $business = Business::where('owner_id', $user->id)->first();
        } elseif ($user->hasRole('seller') && $user->business_id) {
            $business = Business::with('owner')->find($user->business_id);
        } else {
            $business = $user->business;
        }

        // Debug business data
        Log::info('Business data:', [
            'business' => $business ? [
                'id' => $business->id,
                'name' => $business->name,
                'owner_id' => $business->owner_id
            ] : null
        ]);

        // Initialize empty collections for sales and activities
        $sales = collect();
        $recentActivity = collect();

        // Only fetch data if user has a business
        if ($business) {
            // Get all sales data
            $sales = Sale::with(['seller', 'branch.business'])
                ->whereHas('branch', function ($q) use ($business) {
                    $q->where('business_id', $business->id);
                })
                ->latest()
            ->get();

            // Debug sales data
            Log::info('Sales data:', [
                'count' => $sales->count(),
                'total_amount' => $sales->sum('amount')
            ]);

            // Get recent activities
            $recentActivity = Activity::with(['user', 'subject', 'causer'])
                ->whereHas('subject', function ($q) use ($business) {
                    $q->where('business_id', $business->id);
                })
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'type' => $activity->type,
                        'data' => $activity->data,
                        'time' => $activity->created_at->diffForHumans(),
                    ];
                });

            // Debug activity data
            Log::info('Activity data:', [
                'count' => $recentActivity->count()
            ]);
        }

        // Calculate stats with null checks
        $stats = [
            'sales' => $sales,
            'sales_today' => $sales->filter(function ($sale) {
                return $sale->created_at->isToday();
            }),
            'total_sales' => $sales->sum('amount'),
            'total_orders' => $sales->count(),
            'average_order_value' => $sales->avg('amount'),
            'active_branches' => $business ? Branch::where('business_id', $business->id)->where('status', 'active')->count() : 0,
            'total_branches' => $business ? Branch::where('business_id', $business->id)->count() : 0,
            'recent_activity' => $recentActivity,
        ];

        // Debug stats
        Log::info('Stats data:', $stats);

        $businessArray = null;
        if ($business) {
            $businessArray = $business->toArray();
            if ($business->relationLoaded('owner') && $business->owner) {
                $businessArray['owner'] = [
                    'name' => $business->owner->name,
                    'email' => $business->owner->email,
                    'phone' => $business->owner->phone ?? null,
                ];
            }
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'name' => $user->name,
            'quote' => $this->getRandomQuote(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'business' => $businessArray,
                'branch' => $user->branch,
            ],
        ]);
    }

    private function calculateOverview($business, $currentStart, $currentEnd, $previousStart, $previousEnd)
    {
        // Current period stats
        $currentSales = Sale::where('business_id', $business->id)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->get();

        $currentTotalSales = $currentSales->sum('amount');
        $currentTotalOrders = $currentSales->count();
        $currentAOV = $currentTotalOrders > 0 ? $currentTotalSales / $currentTotalOrders : 0;

        // Previous period stats
        $previousSales = Sale::where('business_id', $business->id)
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->get();

        $previousTotalSales = $previousSales->sum('amount');
        $previousTotalOrders = $previousSales->count();
        $previousAOV = $previousTotalOrders > 0 ? $previousTotalSales / $previousTotalOrders : 0;

        // Calculate growth rates
        $salesGrowth = $previousTotalSales != 0 ? (($currentTotalSales - $previousTotalSales) / $previousTotalSales) * 100 : 0;
        $ordersGrowth = $previousTotalOrders != 0 ? (($currentTotalOrders - $previousTotalOrders) / $previousTotalOrders) * 100 : 0;
        $aovGrowth = $previousAOV != 0 ? (($currentAOV - $previousAOV) / $previousAOV) * 100 : 0;

        // Get branch stats
        $activeBranches = Branch::where('business_id', $business->id)
            ->where('status', 'active')
            ->count();

        $totalBranches = Branch::where('business_id', $business->id)->count();

        return [
            'total_sales' => $currentTotalSales,
            'total_orders' => $currentTotalOrders,
            'average_order_value' => $currentAOV,
            'sales_growth' => round($salesGrowth, 2),
            'orders_growth' => round($ordersGrowth, 2),
            'aov_growth' => round($aovGrowth, 2),
            'active_branches' => $activeBranches,
            'total_branches' => $totalBranches
        ];
    }

    private function getSalesTrend($business, $startDate, $endDate)
    {
        return Sale::where('business_id', $business->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as amount'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

    private function getBranchPerformance($business, $start, $end)
    {
        return Sale::where('business_id', $business->id)
            ->whereBetween('created_at', [$start, $end])
            ->select(
                'branch_id',
                DB::raw('SUM(amount) as sales')
            )
            ->with('branch:id,name')
            ->groupBy('branch_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->branch->name,
                    'sales' => $item->sales
                ];
            });
    }

    private function getTopProducts($business, $start, $end)
    {
        return Sale::where('business_id', $business->id)
            ->whereBetween('created_at', [$start, $end])
            ->with(['items.product.inventoryItem'])
            ->get()
            ->flatMap(function ($sale) {
                return $sale->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'name' => $item->product->inventoryItem->name ?? 'Unknown Product',
                        'quantity' => $item->quantity,
                        'revenue' => $item->total_price
                    ];
                });
            })
            ->groupBy('product_id')
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'id' => $first['product_id'],
                    'name' => $first['name'],
                    'quantity' => $items->sum('quantity'),
                    'revenue' => $items->sum('revenue')
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values()
            ->map(function ($item, $index) {
                return array_merge($item, ['rank' => $index + 1]);
            });
    }

    private function getPaymentMethods($business, $start, $end)
    {
        return Sale::where('business_id', $business->id)
            ->whereBetween('created_at', [$start, $end])
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->pluck('count', 'payment_method')
            ->toArray();
    }

    private function getRecentActivity($business)
    {
        $activities = collect();

        // Recent sales
        $recentSales = Sale::where('business_id', $business->id)
            ->with(['branch', 'seller'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => 'sale_' . $sale->id,
                    'title' => "New sale of " . number_format($sale->amount, 2) . " at " . $sale->branch->name,
                    'time' => $sale->created_at->diffForHumans(),
                    'icon' => 'CurrencyDollarIcon',
                    'icon_bg' => 'bg-green-50',
                    'icon_color' => 'text-green-600'
                ];
            });

        // Recent branch activities
        $recentBranches = Branch::where('business_id', $business->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($branch) {
                return [
                    'id' => 'branch_' . $branch->id,
                    'title' => "New branch added: " . $branch->name,
                    'time' => $branch->created_at->diffForHumans(),
                    'icon' => 'BuildingOfficeIcon',
                    'icon_bg' => 'bg-blue-50',
                    'icon_color' => 'text-blue-600'
                ];
            });

        return $activities->concat($recentSales)->concat($recentBranches)
            ->sortByDesc('time')
            ->take(5)
            ->values();
    }

    private function getDateRange($filter, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();
        $currentStart = null;
        $currentEnd = null;
        $previousStart = null;
        $previousEnd = null;

        switch ($filter) {
            case 'today':
                $currentStart = $now->copy()->startOfDay();
                $currentEnd = $now->copy()->endOfDay();
                $previousStart = $now->copy()->subDay()->startOfDay();
                $previousEnd = $now->copy()->subDay()->endOfDay();
                break;

            case 'yesterday':
                $currentStart = $now->copy()->subDay()->startOfDay();
                $currentEnd = $now->copy()->subDay()->endOfDay();
                $previousStart = $now->copy()->subDays(2)->startOfDay();
                $previousEnd = $now->copy()->subDays(2)->endOfDay();
                break;

            case 'this_week':
                $currentStart = $now->copy()->startOfWeek();
                $currentEnd = $now->copy()->endOfWeek();
                $previousStart = $now->copy()->subWeek()->startOfWeek();
                $previousEnd = $now->copy()->subWeek()->endOfWeek();
                break;

            case 'last_week':
                $currentStart = $now->copy()->subWeek()->startOfWeek();
                $currentEnd = $now->copy()->subWeek()->endOfWeek();
                $previousStart = $now->copy()->subWeeks(2)->startOfWeek();
                $previousEnd = $now->copy()->subWeeks(2)->endOfWeek();
                break;

            case 'this_month':
                $currentStart = $now->copy()->startOfMonth();
                $currentEnd = $now->copy()->endOfMonth();
                $previousStart = $now->copy()->subMonth()->startOfMonth();
                $previousEnd = $now->copy()->subMonth()->endOfMonth();
                break;

            case 'last_month':
                $currentStart = $now->copy()->subMonth()->startOfMonth();
                $currentEnd = $now->copy()->subMonth()->endOfMonth();
                $previousStart = $now->copy()->subMonths(2)->startOfMonth();
                $previousEnd = $now->copy()->subMonths(2)->endOfMonth();
                break;

            case 'this_year':
                $currentStart = $now->copy()->startOfYear();
                $currentEnd = $now->copy()->endOfYear();
                $previousStart = $now->copy()->subYear()->startOfYear();
                $previousEnd = $now->copy()->subYear()->endOfYear();
                break;

            case 'last_year':
                $currentStart = $now->copy()->subYear()->startOfYear();
                $currentEnd = $now->copy()->subYear()->endOfYear();
                $previousStart = $now->copy()->subYears(2)->startOfYear();
                $previousEnd = $now->copy()->subYears(2)->endOfYear();
                break;

            case 'custom':
                if ($startDate && $endDate) {
                    $currentStart = Carbon::parse($startDate)->startOfDay();
                    $currentEnd = Carbon::parse($endDate)->endOfDay();
                    $duration = $currentEnd->diffInDays($currentStart);
                    $previousStart = $currentStart->copy()->subDays($duration + 1);
                    $previousEnd = $currentStart->copy()->subDay();
                }
                break;

            default:
                $currentStart = $now->copy()->startOfDay();
                $currentEnd = $now->copy()->endOfDay();
                $previousStart = $now->copy()->subDay()->startOfDay();
                $previousEnd = $now->copy()->subDay()->endOfDay();
        }

        return [
            'current_start' => $currentStart,
            'current_end' => $currentEnd,
            'previous_start' => $previousStart,
            'previous_end' => $previousEnd
        ];
    }

    private function getRandomQuote()
    {
        $quotes = [
            [
                'text' => 'Success is not final, failure is not fatal: it is the courage to continue that counts.',
                'author' => 'Winston Churchill'
            ],
            [
                'text' => 'The only way to do great work is to love what you do.',
                'author' => 'Steve Jobs'
            ],
            [
                'text' => 'Innovation distinguishes between a leader and a follower.',
                'author' => 'Steve Jobs'
            ],
            [
                'text' => 'Your work is going to fill a large part of your life, and the only way to be truly satisfied is to do what you believe is great work.',
                'author' => 'Steve Jobs'
            ],
            [
                'text' => 'The future belongs to those who believe in the beauty of their dreams.',
                'author' => 'Eleanor Roosevelt'
            ],
            [
                'text' => 'Don\'t watch the clock; do what it does. Keep going.',
                'author' => 'Sam Levenson'
            ],
            [
                'text' => 'The harder you work for something, the greater you\'ll feel when you achieve it.',
                'author' => 'Unknown'
            ],
            [
                'text' => 'Success is walking from failure to failure with no loss of enthusiasm.',
                'author' => 'Winston Churchill'
            ],
        ];

        return $quotes[array_rand($quotes)];
    }
} 