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
        $user = auth()->user();
        
        // Get businesses the user owns or manages
        $userBusinesses = Business::where('owner_id', $user->id)
            ->orWhereHas('admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');
        
        // Debug: Log user's businesses
        \Log::info("User ID: " . $user->id . ", User businesses: " . $userBusinesses->implode(', '));
        
        // Load sales data only for user's businesses
        $query = Sale::query()
            ->whereIn('business_id', $userBusinesses)
            ->with(['business', 'branch', 'seller', 'items.product.inventoryItem']);

        $sales = $query->latest()->get();
        
        // Debug: Check what's being loaded
        \Log::info("Total sales for user: " . $sales->count());
        foreach ($sales->take(3) as $sale) {
            \Log::info("Sale ID: " . $sale->id . ", Business: " . ($sale->business->name ?? 'null') . ", Items count: " . ($sale->items ? $sale->items->count() : 'null'));
            
            // Debug product relationships
            if ($sale->items) {
                foreach ($sale->items->take(2) as $item) {
                    \Log::info("Item ID: " . $item->id . ", Product ID: " . $item->product_id);
                    \Log::info("Product name: " . ($item->product->name ?? 'null'));
                    \Log::info("Product inventory_item_id: " . ($item->product->inventory_item_id ?? 'null'));
                    \Log::info("InventoryItem name: " . ($item->product->inventoryItem->name ?? 'null'));
                }
            }
        }

        // Summary calculations for user's businesses only
        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('amount');
        
        // Top products calculation for user's businesses - group by inventory_item_id to avoid duplicates
        $topProductsQuery = \DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereIn('sales.business_id', $userBusinesses)
            ->select('inventory_items.id', 'inventory_items.name', \DB::raw('SUM(sale_items.quantity) as qty'))
            ->groupBy('inventory_items.id', 'inventory_items.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();
        
        // Top sellers calculation for user's businesses
        $topSellersQuery = Sale::whereIn('business_id', $userBusinesses)
            ->select('seller_id', \DB::raw('COUNT(*) as sales_count'))
            ->groupBy('seller_id')
            ->orderByDesc('sales_count')
            ->limit(5)
            ->with('seller')
            ->get();
            
        // Sales by business for user's businesses
        $salesByBusiness = Sale::whereIn('business_id', $userBusinesses)
            ->select('business_id', \DB::raw('SUM(amount) as revenue'))
            ->groupBy('business_id')
            ->with('business')
            ->get();

        // Format data for summary cards
        $topProducts = $topProductsQuery->map(function($item) {
            return ($item->name ?? 'Unknown Product') . ' (' . $item->qty . ' sold)';
        });
        $topSellers = $topSellersQuery->map(function($item) {
            return ($item->seller->name ?? 'Unknown Seller') . ' (' . $item->sales_count . ' sales)';
        });

        // Load data for filters - only user's businesses
        $businesses = Business::whereIn('id', $userBusinesses)->get(['id', 'name']);
        $branches = \App\Models\Branch::whereIn('business_id', $userBusinesses)->get(['id', 'name', 'business_id']);
        $sellers = User::role('seller')->whereIn('business_id', $userBusinesses)->with(['business', 'branch'])->get(['id', 'name', 'business_id', 'branch_id']);
        $products = Product::with(['branch.business'])->get(['id', 'name', 'branch_id']);

        return Inertia::render('Reports/Index', [
            'sales' => $sales->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'business_id' => $sale->business_id,
                    'branch_id' => $sale->branch_id,
                    'reference' => $sale->reference,
                    'created_at' => $sale->created_at,
                    'amount' => $sale->amount,
                    'seller' => $sale->seller ? [
                        'id' => $sale->seller->id,
                        'name' => $sale->seller->name
                    ] : null,
                    'branch' => $sale->branch ? [
                        'id' => $sale->branch->id,
                        'name' => $sale->branch->name
                    ] : null,
                    'business' => $sale->business ? [
                        'id' => $sale->business->id,
                        'name' => $sale->business->name
                    ] : null,
                    'payment_method' => $sale->payment_method,
                    'status' => $sale->status,
                    'items' => $sale->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total' => $item->total,
                            'product' => [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'display_name' => $item->product->display_name,
                                'buying_price' => $item->product->buying_price,
                                'selling_price' => $item->product->selling_price,
                                'barcode' => $item->product->barcode,
                                'inventoryItem' => $item->product->inventoryItem ? [
                                    'id' => $item->product->inventoryItem->id,
                                    'name' => $item->product->inventoryItem->name,
                                    'description' => $item->product->inventoryItem->description
                                ] : null
                            ]
                        ];
                    })
                ];
            }),
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
        $user = auth()->user();
        
        // Get businesses the user owns or manages
        $userBusinesses = Business::where('owner_id', $user->id)
            ->orWhereHas('admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');
        
        $query = Sale::query()
            ->whereIn('business_id', $userBusinesses)
            ->with(['business', 'branch', 'seller', 'items.product.inventoryItem'])
            ->when($request->business_id, fn($q) => $q->where('business_id', $request->business_id))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->seller_id, fn($q) => $q->where('seller_id', $request->seller_id))
            ->when($request->product_id, function($q) use ($request) {
                $q->whereHas('items', fn($q2) => $q2->where('product_id', $request->product_id));
            })
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to));

        $sales = $query->latest()->get();

        if ($request->format === 'pdf') {
            // Get business info (assuming first sale's business)
            $business = $sales->first() ? $sales->first()->business : null;
            
            // Calculate summary statistics
            $summary = [
                'total_sales' => $sales->count(),
                'total_amount' => $sales->sum('amount'),
                'average_amount' => $sales->avg('amount'),
                'payment_methods' => $sales->groupBy('payment_method')
                    ->map(fn($group) => [
                        'count' => $group->count(),
                        'amount' => $group->sum('amount')
                    ])
            ];
            
            $pdf = Pdf::loadView('pdfs.sales-report', [
                'sales' => $sales,
                'business' => $business,
                'summary' => $summary,
                'startDate' => $request->date_from,
                'endDate' => $request->date_to
            ]);
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
                    // Get product name from inventory_item, not from product table
                    $productName = $item->product->inventoryItem->name ?? 'Unknown Product';
                    $buyingPrice = $item->product->buying_price ?? 0;
                    $sellingPrice = $item->unit_price ?? 0;
                    $profit = $sellingPrice - $buyingPrice;
                    $totalProfit = $profit * $item->quantity;
                    return $productName . ' (' . $item->quantity . ' x KES ' . $sellingPrice . ', Buy: KES ' . $buyingPrice . ', Profit: KES ' . number_format($totalProfit, 2) . ')';
                })->join(', ');
                
                fputcsv($file, [
                    $sale->created_at->format('Y-m-d H:i:s'),
                    $sale->business->name ?? 'Unknown Business',
                    $sale->branch->name ?? 'Unknown Branch',
                    $products,
                    $sale->amount,
                    $sale->payment_method,
                    $sale->seller->name ?? 'Unknown Seller',
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