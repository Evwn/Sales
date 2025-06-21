<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Business;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Debug: Check if there are any sale items in the database
        $saleItemsCount = SaleItem::count();
        \Log::info("Total sale items in database: " . $saleItemsCount);
        
        // Load ALL sales data initially
        $query = Sale::query()
            ->with(['business', 'branch', 'seller', 'items.product']);

        $sales = $query->latest()->get(); // Get all sales, not paginated
        
        // Debug: Check what's being loaded
        foreach ($sales->take(3) as $sale) {
            \Log::info("Sale ID: " . $sale->id . ", Items count: " . ($sale->items ? $sale->items->count() : 'null'));
        }

        // Summary calculations
        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('amount');
        
        // Top products calculation
        $topProductsQuery = SaleItem::select('product_id', \DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_id')->orderByDesc('qty')->limit(5)->with('product')->get();
        $topSellersQuery = Sale::select('seller_id', \DB::raw('COUNT(*) as sales_count'))
            ->groupBy('seller_id')->orderByDesc('sales_count')->limit(5)->with('seller')->get();
        $salesByBusiness = Sale::select('business_id', \DB::raw('SUM(amount) as revenue'))
            ->groupBy('business_id')->with('business')->get();

        // Format data for summary cards
        $topProducts = $topProductsQuery->map(fn($item) => $item->product->name . ' (' . $item->qty . ' sold)');
        $topSellers = $topSellersQuery->map(fn($item) => $item->seller->name . ' (' . $item->sales_count . ' sales)');

        // Load ALL data for filters
        $businesses = Business::all(['id', 'name']);
        $branches = \App\Models\Branch::all(['id', 'name', 'business_id']);
        $sellers = User::role('seller')->with(['business', 'branch'])->get(['id', 'name', 'business_id', 'branch_id']);
        $products = Product::with(['business', 'branch'])->get(['id', 'name', 'business_id', 'branch_id']);

        return Inertia::render('Reports/Index', [
            'sales' => $sales,
            'summary' => [
                'totalSales' => $totalSales,
                'totalRevenue' => $totalRevenue,
                'topProducts' => $topProducts,
                'topSellers' => $topSellers,
                'salesByBusiness' => $salesByBusiness,
            ],
            'filtersData' => [
                'businesses' => $businesses,
                'branches' => $branches,
                'sellers' => $sellers,
                'products' => $products,
            ]
        ]);
    }

    public function export(Request $request)
    {
        $query = Sale::query()
            ->with(['business', 'branch', 'seller', 'items.product'])
            ->when($request->business_id, fn($q) => $q->where('business_id', $request->business_id))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->seller_id, fn($q) => $q->where('seller_id', $request->seller_id))
            ->when($request->product_id, function($q) use ($request) {
                $q->whereHas('items', fn($q2) => $q2->where('product_id', $request->product_id));
            })
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('sale_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('sale_date', '<=', $request->date_to));

        $sales = $query->latest()->get();

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('reports.export', ['sales' => $sales]);
            return $pdf->download('sales-report.pdf');
        }

        // Default to CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=sales-report.csv',
        ];
        $callback = function () use ($sales) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Date', 'Business', 'Branch', 'Products', 'Total Amount', 'Payment Method', 'Seller',
            ]);
            foreach ($sales as $sale) {
                $products = $sale->items->map(function ($item) {
                    return $item->product->name . ' (' . $item->quantity . ' x $' . $item->unit_price . ')';
                })->join(', ');
                fputcsv($file, [
                    $sale->created_at->format('Y-m-d H:i:s'),
                    $sale->business->name ?? '',
                    $sale->branch->name ?? '',
                    $products,
                    $sale->amount,
                    $sale->payment_method,
                    $sale->seller->name ?? '',
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function overview()
    {
        return Inertia::render('Reports/Index', [
            'reports' => [
                'total_sales' => Sale::count(),
                'total_revenue' => Sale::sum('total_amount'),
                'recent_sales' => Sale::with(['branch.business', 'seller'])
                    ->latest()
                    ->take(5)
                    ->get(),
            ],
        ]);
    }
} 