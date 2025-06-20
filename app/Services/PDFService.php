<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Business;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PDFService
{
    public function generateReceiptPDF(Sale $sale)
    {
        $sale->load(['customer', 'seller', 'business', 'branch', 'items.product']);
        
        $pdf = PDF::loadView('pdfs.receipt', [
            'sale' => $sale,
            'business' => $sale->business,
            'branch' => $sale->branch
        ]);

        return $pdf;
    }

    public function generateMultipleReceiptsPDF(Collection $sales)
    {
        $sales->load(['customer', 'seller', 'business', 'branch', 'items.product']);
        
        $pdf = PDF::loadView('pdfs.multiple-receipts', [
            'sales' => $sales,
            'business' => $sales->first()->business,
            'branch' => $sales->first()->branch
        ]);

        return $pdf;
    }

    public function generateSalesReportPDF(Business $business, $startDate = null, $endDate = null, $branchId = null)
    {
        $query = Sale::with(['customer', 'seller', 'branch', 'items.product'])
            ->where('business_id', $business->id);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $sales = $query->get();
        
        $summary = [
            'total_sales' => $sales->count(),
            'total_amount' => $sales->sum('total_amount'),
            'average_amount' => $sales->avg('total_amount'),
            'payment_methods' => $sales->groupBy('payment_method')
                ->map(fn($group) => [
                    'count' => $group->count(),
                    'amount' => $group->sum('total_amount')
                ])
        ];

        $pdf = PDF::loadView('pdfs.sales-report', [
            'sales' => $sales,
            'business' => $business,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        return $pdf;
    }

    public function generateDailyReportPDF(Business $business, $date = null, $branchId = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $query = $this->getBaseQuery($business, $date->startOfDay(), $date->copy()->endOfDay(), $branchId);
        
        $sales = $query->get();
        $summary = $this->generateSummary($sales);
        $hourlyStats = $this->getHourlyStats($sales);
        $productStats = $this->getProductStats($sales);
        
        $pdf = PDF::loadView('pdfs.daily-report', [
            'sales' => $sales,
            'business' => $business,
            'summary' => $summary,
            'hourlyStats' => $hourlyStats,
            'productStats' => $productStats,
            'date' => $date->format('Y-m-d')
        ]);

        return $pdf;
    }

    public function generateWeeklyReportPDF(Business $business, $startDate = null, $branchId = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();
        
        $query = $this->getBaseQuery($business, $startDate, $endDate, $branchId);
        $sales = $query->get();
        
        $summary = $this->generateSummary($sales);
        $dailyStats = $this->getDailyStats($sales);
        $productStats = $this->getProductStats($sales);
        
        $pdf = PDF::loadView('pdfs.weekly-report', [
            'sales' => $sales,
            'business' => $business,
            'summary' => $summary,
            'dailyStats' => $dailyStats,
            'productStats' => $productStats,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ]);

        return $pdf;
    }

    public function generateMonthlyReportPDF(Business $business, $year = null, $month = null, $branchId = null)
    {
        $date = Carbon::create($year ?? now()->year, $month ?? now()->month, 1);
        $startDate = $date->startOfMonth();
        $endDate = $date->copy()->endOfMonth();
        
        $query = $this->getBaseQuery($business, $startDate, $endDate, $branchId);
        $sales = $query->get();
        
        $summary = $this->generateSummary($sales);
        $dailyStats = $this->getDailyStats($sales);
        $productStats = $this->getProductStats($sales);
        $branchStats = $this->getBranchStats($sales);
        
        $pdf = PDF::loadView('pdfs.monthly-report', [
            'sales' => $sales,
            'business' => $business,
            'summary' => $summary,
            'dailyStats' => $dailyStats,
            'productStats' => $productStats,
            'branchStats' => $branchStats,
            'month' => $date->format('F Y')
        ]);

        return $pdf;
    }

    public function generateQuarterlyReportPDF(Business $business, $year = null, $quarter = null, $branchId = null)
    {
        $year = $year ?? now()->year;
        $quarter = $quarter ?? ceil(now()->month / 3);
        
        $startDate = Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfMonth();
        $endDate = $startDate->copy()->addMonths(2)->endOfMonth();
        
        $query = $this->getBaseQuery($business, $startDate, $endDate, $branchId);
        $sales = $query->get();
        
        $summary = $this->generateSummary($sales);
        $monthlyStats = $this->getMonthlyStats($sales);
        $productStats = $this->getProductStats($sales);
        $branchStats = $this->getBranchStats($sales);
        $categoryStats = $this->getCategoryStats($sales);
        $trendAnalysis = $this->getTrendAnalysis($sales);
        
        $pdf = PDF::loadView('pdfs.quarterly-report', [
            'sales' => $sales,
            'business' => $business,
            'summary' => $summary,
            'monthlyStats' => $monthlyStats,
            'productStats' => $productStats,
            'branchStats' => $branchStats,
            'categoryStats' => $categoryStats,
            'trendAnalysis' => $trendAnalysis,
            'quarter' => $quarter,
            'year' => $year
        ]);

        return $pdf;
    }

    public function generateYearlyReportPDF(Business $business, $year = null, $branchId = null)
    {
        $year = $year ?? now()->year;
        $startDate = Carbon::create($year, 1, 1)->startOfYear();
        $endDate = $startDate->copy()->endOfYear();
        
        $query = $this->getBaseQuery($business, $startDate, $endDate, $branchId);
        $sales = $query->get();
        
        $summary = $this->generateSummary($sales);
        $quarterlyStats = $this->getQuarterlyStats($sales);
        $monthlyStats = $this->getMonthlyStats($sales);
        $productStats = $this->getProductStats($sales);
        $branchStats = $this->getBranchStats($sales);
        $categoryStats = $this->getCategoryStats($sales);
        $trendAnalysis = $this->getTrendAnalysis($sales);
        $yearOverYear = $this->getYearOverYearComparison($business, $year, $branchId);
        
        $pdf = PDF::loadView('pdfs.yearly-report', [
            'sales' => $sales,
            'business' => $business,
            'summary' => $summary,
            'quarterlyStats' => $quarterlyStats,
            'monthlyStats' => $monthlyStats,
            'productStats' => $productStats,
            'branchStats' => $branchStats,
            'categoryStats' => $categoryStats,
            'trendAnalysis' => $trendAnalysis,
            'yearOverYear' => $yearOverYear,
            'year' => $year
        ]);

        return $pdf;
    }

    public function generateBatchReceiptsPDF(Collection $sales, $options = [])
    {
        $sales->load(['customer', 'seller', 'business', 'branch', 'items.product']);
        
        $defaultOptions = [
            'include_summary' => true,
            'group_by_branch' => false,
            'include_charts' => true,
            'page_break_between' => true,
            'custom_header' => null,
            'custom_footer' => null
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        $pdf = PDF::loadView('pdfs.batch-receipts', [
            'sales' => $sales,
            'business' => $sales->first()->business,
            'branch' => $sales->first()->branch,
            'options' => $options,
            'summary' => $options['include_summary'] ? $this->generateSummary($sales) : null,
            'branchStats' => $options['group_by_branch'] ? $this->getBranchStats($sales) : null
        ]);

        return $pdf;
    }

    private function getBaseQuery(Business $business, $startDate, $endDate, $branchId = null)
    {
        $query = Sale::with(['customer', 'seller', 'branch', 'items.product'])
            ->where('business_id', $business->id)
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    private function generateSummary($sales)
    {
        return [
            'total_sales' => $sales->count(),
            'total_amount' => $sales->sum('total_amount'),
            'average_amount' => $sales->avg('total_amount'),
            'payment_methods' => $sales->groupBy('payment_method')
                ->map(fn($group) => [
                    'count' => $group->count(),
                    'amount' => $group->sum('total_amount')
                ]),
            'total_items' => $sales->sum(fn($sale) => $sales->sum(fn($item) => $item->quantity)),
            'average_items_per_sale' => $sales->avg(fn($sale) => $sales->sum(fn($item) => $item->quantity))
        ];
    }

    private function getHourlyStats($sales)
    {
        return $sales->groupBy(fn($sale) => $sale->created_at->format('H'))
            ->map(fn($group) => [
                'count' => $group->count(),
                'amount' => $group->sum('total_amount')
            ])
            ->sortKeys();
    }

    private function getDailyStats($sales)
    {
        return $sales->groupBy(fn($sale) => $sale->created_at->format('Y-m-d'))
            ->map(fn($group) => [
                'count' => $group->count(),
                'amount' => $group->sum('total_amount')
            ])
            ->sortKeys();
    }

    private function getProductStats($sales)
    {
        $productStats = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $productId = $item->product_id;
                if (!isset($productStats[$productId])) {
                    $productStats[$productId] = [
                        'name' => $item->product->name,
                        'quantity' => 0,
                        'amount' => 0
                    ];
                }
                $productStats[$productId]['quantity'] += $item->quantity;
                $productStats[$productId]['amount'] += $item->total_price;
            }
        }
        return collect($productStats)->sortByDesc('amount');
    }

    private function getBranchStats($sales)
    {
        return $sales->groupBy('branch_id')
            ->map(fn($group) => [
                'name' => $group->first()->branch->name,
                'count' => $group->count(),
                'amount' => $group->sum('total_amount')
            ])
            ->sortByDesc('amount');
    }

    private function getMonthlyStats($sales)
    {
        return $sales->groupBy(fn($sale) => $sale->created_at->format('Y-m'))
            ->map(fn($group) => [
                'count' => $group->count(),
                'amount' => $group->sum('total_amount'),
                'items' => $group->sum(fn($sale) => $sale->items->sum('quantity'))
            ])
            ->sortKeys();
    }

    private function getQuarterlyStats($sales)
    {
        return $sales->groupBy(fn($sale) => ceil($sale->created_at->month / 3))
            ->map(fn($group) => [
                'count' => $group->count(),
                'amount' => $group->sum('total_amount'),
                'items' => $group->sum(fn($sale) => $sale->items->sum('quantity'))
            ])
            ->sortKeys();
    }

    private function getCategoryStats($sales)
    {
        $categoryStats = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $categoryId = $item->product->category_id;
                if (!isset($categoryStats[$categoryId])) {
                    $categoryStats[$categoryId] = [
                        'name' => $item->product->category->name,
                        'quantity' => 0,
                        'amount' => 0,
                        'products' => []
                    ];
                }
                $categoryStats[$categoryId]['quantity'] += $item->quantity;
                $categoryStats[$categoryId]['amount'] += $item->total_price;
                
                // Track top products in category
                $productId = $item->product_id;
                if (!isset($categoryStats[$categoryId]['products'][$productId])) {
                    $categoryStats[$categoryId]['products'][$productId] = [
                        'name' => $item->product->name,
                        'quantity' => 0,
                        'amount' => 0
                    ];
                }
                $categoryStats[$categoryId]['products'][$productId]['quantity'] += $item->quantity;
                $categoryStats[$categoryId]['products'][$productId]['amount'] += $item->total_price;
            }
        }
        
        // Sort categories by amount and products within categories
        foreach ($categoryStats as &$category) {
            uasort($category['products'], fn($a, $b) => $b['amount'] <=> $a['amount']);
            $category['products'] = array_slice($category['products'], 0, 5); // Keep top 5 products
        }
        
        return collect($categoryStats)->sortByDesc('amount');
    }

    private function getTrendAnalysis($sales)
    {
        $trends = [
            'daily_growth' => $this->calculateGrowthRate($sales, 'day'),
            'weekly_growth' => $this->calculateGrowthRate($sales, 'week'),
            'monthly_growth' => $this->calculateGrowthRate($sales, 'month'),
            'peak_hours' => $this->getPeakHours($sales),
            'peak_days' => $this->getPeakDays($sales),
            'seasonal_trends' => $this->getSeasonalTrends($sales)
        ];
        
        return $trends;
    }

    private function getYearOverYearComparison($business, $currentYear, $branchId = null)
    {
        $previousYear = $currentYear - 1;
        
        $currentYearSales = $this->getBaseQuery($business, 
            Carbon::create($currentYear, 1, 1)->startOfYear(),
            Carbon::create($currentYear, 12, 31)->endOfYear(),
            $branchId
        )->get();
        
        $previousYearSales = $this->getBaseQuery($business,
            Carbon::create($previousYear, 1, 1)->startOfYear(),
            Carbon::create($previousYear, 12, 31)->endOfYear(),
            $branchId
        )->get();
        
        return [
            'current_year' => [
                'total_sales' => $currentYearSales->count(),
                'total_amount' => $currentYearSales->sum('total_amount'),
                'average_amount' => $currentYearSales->avg('total_amount')
            ],
            'previous_year' => [
                'total_sales' => $previousYearSales->count(),
                'total_amount' => $previousYearSales->sum('total_amount'),
                'average_amount' => $previousYearSales->avg('total_amount')
            ],
            'growth' => [
                'sales_growth' => $this->calculateGrowthPercentage(
                    $previousYearSales->count(),
                    $currentYearSales->count()
                ),
                'amount_growth' => $this->calculateGrowthPercentage(
                    $previousYearSales->sum('total_amount'),
                    $currentYearSales->sum('total_amount')
                ),
                'average_growth' => $this->calculateGrowthPercentage(
                    $previousYearSales->avg('total_amount'),
                    $currentYearSales->avg('total_amount')
                )
            ]
        ];
    }

    private function calculateGrowthRate($sales, $period)
    {
        $grouped = $sales->groupBy(fn($sale) => $sale->created_at->startOf($period)->format('Y-m-d'));
        $amounts = $grouped->map(fn($group) => $group->sum('total_amount'))->values();
        
        if ($amounts->count() < 2) {
            return 0;
        }
        
        $first = $amounts->first();
        $last = $amounts->last();
        
        return $first != 0 ? (($last - $first) / $first) * 100 : 0;
    }

    private function getPeakHours($sales)
    {
        return $sales->groupBy(fn($sale) => $sale->created_at->format('H'))
            ->map(fn($group) => [
                'count' => $group->count(),
                'amount' => $group->sum('total_amount')
            ])
            ->sortByDesc('amount')
            ->take(3);
    }

    private function getPeakDays($sales)
    {
        return $sales->groupBy(fn($sale) => $sale->created_at->format('l'))
            ->map(fn($group) => [
                'count' => $group->count(),
                'amount' => $group->sum('total_amount')
            ])
            ->sortByDesc('amount');
    }

    private function getSeasonalTrends($sales)
    {
        return $sales->groupBy(fn($sale) => $sale->created_at->format('m'))
            ->map(fn($group) => [
                'count' => $group->count(),
                'amount' => $group->sum('total_amount')
            ])
            ->sortKeys();
    }

    private function calculateGrowthPercentage($previous, $current)
    {
        return $previous != 0 ? (($current - $previous) / $previous) * 100 : 0;
    }
} 