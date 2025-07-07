<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Business;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\User;
use App\Models\SalesReceiptItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            // For admin users, get all businesses
            $userBusinesses = Business::pluck('id');
            $sales = Sale::with(['business', 'branch', 'seller', 'items.product.inventoryItem'])->latest()->get();
            $businesses = Business::all(['id', 'name']);
            $branches = \App\Models\Branch::all(['id', 'name', 'business_id']);
            $sellers = User::role('seller')->with(['business', 'branch'])->get(['id', 'name', 'business_id', 'branch_id']);
            $products = Product::with(['branch.business'])->get(['id', 'name', 'branch_id']);
        } else {
            // Get businesses the user owns or manages
            $userBusinesses = Business::where('owner_id', $user->id)
                ->orWhereHas('admins', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->pluck('id');
            // Load sales data only for user's businesses
            $query = Sale::query()
                ->whereIn('business_id', $userBusinesses)
                ->with(['business', 'branch', 'seller', 'items.product.inventoryItem']);
            $sales = $query->latest()->get();
            $businesses = Business::whereIn('id', $userBusinesses)->get(['id', 'name']);
            $branches = \App\Models\Branch::whereIn('business_id', $userBusinesses)->get(['id', 'name', 'business_id']);
            $sellers = User::role('seller')->whereIn('business_id', $userBusinesses)->with(['business', 'branch'])->get(['id', 'name', 'business_id', 'branch_id']);
            $products = Product::with(['branch.business'])->get(['id', 'name', 'branch_id']);
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

        // Per Item Sales from sales_receipt_items, join products and inventory_items to get unit
        $perItemSalesData = SalesReceiptItem::select(
                'sales_receipt_items.product_name',
                \DB::raw('SUM(sales_receipt_items.quantity) as qty'),
                'inventory_items.unit as unit'
            )
            ->leftJoin('products', 'sales_receipt_items.product_id', '=', 'products.id')
            ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
            ->groupBy('sales_receipt_items.product_name', 'inventory_items.unit')
            ->orderByDesc('qty')
            ->get();

        // Items Report data: get product name (from products or inventory_items) and stock
        $itemsReportData = \DB::table('products')
            ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
            ->selectRaw('COALESCE(products.name, inventory_items.name) as name, products.stock')
            ->get();

        // Stock & Item Valuation data: get product name (from products or inventory_items), stock, buying price, and price
        $itemsValuationData = \DB::table('products')
            ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
            ->selectRaw('COALESCE(products.name, inventory_items.name) as name, products.stock, products.buying_price, products.price')
            ->get();

        // Products for filter: get from sales_receipt_items, grouped by product_id, product_name, and branch
        $products = \DB::table('sales_receipt_items')
            ->join('products', 'sales_receipt_items.product_id', '=', 'products.id')
            ->join('branches', 'products.branch_id', '=', 'branches.id')
            ->select(
                'sales_receipt_items.product_id as id',
                \DB::raw("CONCAT(sales_receipt_items.product_name, ' (', branches.name, ')') as name"),
                'products.branch_id',
                'branches.business_id as business_id'
            )
            ->groupBy('sales_receipt_items.product_id', 'sales_receipt_items.product_name', 'branches.name', 'products.branch_id', 'branches.business_id')
            ->orderBy('sales_receipt_items.product_name')
            ->get();

        return Inertia::render('Reports/Index', [
            'sales' => $sales->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'business_id' => $sale->business_id,
                    'branch_id' => $sale->branch_id,
                    'seller_id' => $sale->seller_id,
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
                        $product = $item->product;
                        $inventoryItemName = null;
                        if ($product && $product->inventoryItem) {
                            $inventoryItemName = $product->inventoryItem->name;
                        }
                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total' => $item->total,
                            'product_name' => $inventoryItemName ?? $product->name ?? 'Unknown Product',
                            'product' => [
                                'id' => $product->id ?? null,
                                'name' => $product->name ?? null,
                                'display_name' => $product->display_name ?? null,
                                'buying_price' => $product->buying_price ?? null,
                                'selling_price' => $product->selling_price ?? null,
                                'barcode' => $product->barcode ?? null,
                                'inventoryItem' => $product->inventoryItem ? [
                                    'id' => $product->inventoryItem->id,
                                    'name' => $product->inventoryItem->name,
                                    'description' => $product->inventoryItem->description
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
            ],
            'perItemSalesData' => $perItemSalesData,
            'itemsReportData' => $itemsReportData,
            'itemsValuationData' => $itemsValuationData,
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
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to));

        $sales = $query->latest()->get();

        $reportType = $request->get('report_type', 'sales');

        // PDF export for each report type
        if ($request->format === 'pdf') {
            if ($reportType === 'valuation') {
                $itemsValuationData = \DB::table('products')
                    ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
                    ->selectRaw('COALESCE(products.name, inventory_items.name) as name, products.stock, products.buying_price, products.price')
                    ->get();
                $pdf = Pdf::loadView('pdfs.stock-item-valuation', [
                    'itemsValuationData' => $itemsValuationData,
                ]);
                return $pdf->download('stock-item-valuation.pdf');
            }
            if ($reportType === 'profit') {
                $profitData = $sales->map(function ($sale) {
                    $profit = $sale->items->reduce(function ($sum, $item) {
                        $buy = $item->product->buying_price ?? 0;
                        $sell = $item->unit_price ?? 0;
                        return $sum + (($sell - $buy) * $item->quantity);
                    }, 0);
                    $products = $sale->items->map(function ($item) {
                        $productName = $item->product->inventoryItem->name ?? $item->product->name ?? $item->product_name ?? 'N/A';
                        return $productName . ' x' . $item->quantity .
                            ' (Buy: KES ' . $item->product->buying_price . ', Sell: KES ' . $item->unit_price . ', Profit: KES ' . number_format(($item->unit_price - $item->product->buying_price) * $item->quantity, 2) . ')';
                    })->join('<br>');
                    return [
                        'date' => $sale->created_at->format('Y-m-d'),
                        'business' => $sale->business->name ?? '',
                        'branch' => $sale->branch->name ?? '',
                        'seller' => $sale->seller->name ?? '',
                        'products' => $products,
                        'profit' => $profit,
                    ];
                });
                $pdf = Pdf::loadView('pdfs.profit-report', [
                    'profitData' => $profitData,
                ]);
                return $pdf->download('profit-report.pdf');
            }
            if ($reportType === 'peritem') {
                $perItemData = SalesReceiptItem::select(
                        'sales_receipt_items.product_name',
                        \DB::raw('SUM(sales_receipt_items.quantity) as qty'),
                        'inventory_items.unit as unit'
                    )
                    ->leftJoin('products', 'sales_receipt_items.product_id', '=', 'products.id')
                    ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
                    ->groupBy('sales_receipt_items.product_name', 'inventory_items.unit')
                    ->orderByDesc('qty')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'product_name' => $item->product_name,
                            'qty' => $item->qty,
                            'unit' => $item->unit,
                        ];
                    });
                $chartImage = $request->input('chart_image') ?? $request->chart_image;
                $pdf = Pdf::loadView('pdfs.per-item-report', [
                    'perItemData' => $perItemData,
                    'chartImage' => $chartImage,
                ]);
                return $pdf->download('per-item-report.pdf');
            }
            if ($reportType === 'items') {
                $itemsData = \DB::table('products')
                    ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
                    ->selectRaw('COALESCE(products.name, inventory_items.name) as name, products.stock')
                    ->get();
                $pdf = Pdf::loadView('pdfs.items-report', [
                    'itemsData' => $itemsData,
                ]);
                return $pdf->download('items-report.pdf');
            }
            if ($reportType === 'stock') {
                $stockData = \DB::table('products')
                    ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
                    ->selectRaw('COALESCE(products.name, inventory_items.name) as name, products.stock')
                    ->get();
                $pdf = Pdf::loadView('pdfs.stock-report', [
                    'stockData' => $stockData,
                ]);
                return $pdf->download('stock-report.pdf');
            }
            if ($reportType === 'pl') {
                $granularity = $request->input('pl_granularity', 'month'); // default to month
                $plByPeriod = [];
                foreach ($sales as $sale) {
                    switch ($granularity) {
                        case 'year':
                            $key = $sale->created_at->format('Y');
                            break;
                        case 'month':
                            $key = $sale->created_at->format('Y-m');
                            break;
                        case 'day':
                            $key = $sale->created_at->format('Y-m-d');
                            break;
                        case 'hour':
                            $key = $sale->created_at->format('Y-m-d H:00');
                            break;
                        default:
                            $key = $sale->created_at->format('Y-m');
                    }
                    $profit = $sale->items->reduce(function ($sum, $item) {
                        $buy = $item->product->buying_price ?? 0;
                        $sell = $item->unit_price ?? 0;
                        return $sum + (($sell - $buy) * $item->quantity);
                    }, 0);
                    if (!isset($plByPeriod[$key])) {
                        $plByPeriod[$key] = 0;
                    }
                    $plByPeriod[$key] += $profit;
                }
                $plData = collect($plByPeriod)->map(function ($profit, $period) {
                    return [
                        'month' => $period,
                        'profit' => $profit,
                    ];
                })->values();
                $pdf = Pdf::loadView('pdfs.profit-loss-report', [
                    'plData' => $plData,
                    'granularity' => $granularity,
                ]);
                return $pdf->download('profit-loss-report.pdf');
            }
            // Default: sales report
            $business = null;
            if ($request->business_id) {
                $business = Business::find($request->business_id);
            } else {
                $uniqueBusinesses = $sales->pluck('business_id')->unique();
                if ($uniqueBusinesses->count() > 1) {
                    $business = null;
                } else {
                    $business = $sales->first() ? $sales->first()->business : null;
                }
            }
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
                'endDate' => $request->date_to,
                'isMultiBusiness' => !$request->business_id && $sales->pluck('business_id')->unique()->count() > 1
            ]);
            return $pdf->download('sales-report.pdf');
        }

        // CSV export for each report type
        if ($reportType === 'valuation') {
            $itemsValuationData = \DB::table('products')
                ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
                ->selectRaw('COALESCE(products.name, inventory_items.name) as name, products.stock, products.buying_price, products.price')
                ->get();
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=stock-item-valuation.csv',
            ];
            $callback = function () use ($itemsValuationData) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Product', 'Stock', 'Buying Price', 'Price', 'Value']);
                foreach ($itemsValuationData as $item) {
                    fputcsv($file, [
                        $item->name,
                        $item->stock,
                        $item->buying_price,
                        $item->price,
                        number_format((float)$item->stock * (float)$item->buying_price, 2)
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }
        if ($reportType === 'profit') {
            $profitData = $sales->map(function ($sale) {
                $profit = $sale->items->reduce(function ($sum, $item) {
                    $buy = $item->product->buying_price ?? 0;
                    $sell = $item->unit_price ?? 0;
                    return $sum + (($sell - $buy) * $item->quantity);
                }, 0);
                $products = $sale->items->map(function ($item) {
                    $productName = $item->product->inventoryItem->name ?? $item->product->name ?? $item->product_name ?? 'N/A';
                    return $productName . ' x' . $item->quantity .
                        ' (Buy: KES ' . $item->product->buying_price . ', Sell: KES ' . $item->unit_price . ', Profit: KES ' . number_format(($item->unit_price - $item->product->buying_price) * $item->quantity, 2) . ')';
                })->join('<br>');
                return [
                    'date' => $sale->created_at->format('Y-m-d'),
                    'business' => $sale->business->name ?? '',
                    'branch' => $sale->branch->name ?? '',
                    'seller' => $sale->seller->name ?? '',
                    'products' => $products,
                    'profit' => $profit,
                ];
            });
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=profit-report.csv',
            ];
            $callback = function () use ($profitData) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Date', 'Business', 'Branch', 'Seller', 'Products', 'Profit']);
                foreach ($profitData as $row) {
                    fputcsv($file, [
                        $row['date'],
                        $row['business'],
                        $row['branch'],
                        $row['seller'],
                        $row['products'],
                        $row['profit'],
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }
        if ($reportType === 'peritem') {
            $perItemData = SalesReceiptItem::select(
                    'sales_receipt_items.product_name',
                    \DB::raw('SUM(sales_receipt_items.quantity) as qty'),
                    'inventory_items.unit as unit'
                )
                ->leftJoin('products', 'sales_receipt_items.product_id', '=', 'products.id')
                ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
                ->groupBy('sales_receipt_items.product_name', 'inventory_items.unit')
                ->orderByDesc('qty')
                ->get()
                ->map(function ($item) {
                    return [
                        'product_name' => $item->product_name,
                        'qty' => $item->qty,
                        'unit' => $item->unit,
                    ];
                });
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=per-item-report.csv',
            ];
            $callback = function () use ($perItemData) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Product', 'Quantity Sold', 'Unit']);
                foreach ($perItemData as $item) {
                    fputcsv($file, [
                        $item->product_name,
                        $item->qty,
                        $item->unit,
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }
        if ($reportType === 'items') {
            $itemsData = \DB::table('products')
                ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
                ->selectRaw('COALESCE(products.name, inventory_items.name) as name, products.stock')
                ->get();
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=items-report.csv',
            ];
            $callback = function () use ($itemsData) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Product', 'Stock']);
                foreach ($itemsData as $item) {
                    fputcsv($file, [
                        $item->name,
                        $item->stock,
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }
        if ($reportType === 'stock') {
            $stockData = \DB::table('products')
                ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
                ->selectRaw('COALESCE(products.name, inventory_items.name) as name, products.stock')
                ->get();
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=stock-report.csv',
            ];
            $callback = function () use ($stockData) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Product', 'Stock']);
                foreach ($stockData as $item) {
                    fputcsv($file, [
                        $item->name,
                        $item->stock,
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }
        if ($reportType === 'pl') {
            $granularity = $request->input('pl_granularity', 'month'); // default to month
            $plByPeriod = [];
            foreach ($sales as $sale) {
                switch ($granularity) {
                    case 'year':
                        $key = $sale->created_at->format('Y');
                        break;
                    case 'month':
                        $key = $sale->created_at->format('Y-m');
                        break;
                    case 'day':
                        $key = $sale->created_at->format('Y-m-d');
                        break;
                    case 'hour':
                        $key = $sale->created_at->format('Y-m-d H:00');
                        break;
                    default:
                        $key = $sale->created_at->format('Y-m');
                }
                $profit = $sale->items->reduce(function ($sum, $item) {
                    $buy = $item->product->buying_price ?? 0;
                    $sell = $item->unit_price ?? 0;
                    return $sum + (($sell - $buy) * $item->quantity);
                }, 0);
                if (!isset($plByPeriod[$key])) {
                    $plByPeriod[$key] = 0;
                }
                $plByPeriod[$key] += $profit;
            }
            $plData = collect($plByPeriod)->map(function ($profit, $period) {
                return [
                    'month' => $period,
                    'profit' => $profit,
                ];
            })->values();
            $pdf = Pdf::loadView('pdfs.profit-loss-report', [
                'plData' => $plData,
                'granularity' => $granularity,
            ]);
            return $pdf->download('profit-loss-report.pdf');
        }

        // Default to sales CSV export
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

    public function email(Request $request)
    {
        $user = auth()->user();
        $reportType = $request->input('report_type', 'sales');
        $filters = $request->input('filters', []);
        $granularity = $request->input('pl_granularity', 'month');

        // Build the sales query and data as in export()
        $query = Sale::query()
            ->whereIn('business_id', Business::where('owner_id', $user->id)
                ->orWhereHas('admins', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->pluck('id'))
            ->with(['business', 'branch', 'seller', 'items.product.inventoryItem']);
        if (!empty($filters['business_id'])) $query->where('business_id', $filters['business_id']);
        if (!empty($filters['branch_id'])) $query->where('branch_id', $filters['branch_id']);
        if (!empty($filters['seller_id'])) $query->where('seller_id', $filters['seller_id']);
        if (!empty($filters['product_id'])) {
            $query->whereHas('items', fn($q2) => $q2->where('product_id', $filters['product_id']));
        }
        if (!empty($filters['date']['start'])) $query->whereDate('created_at', '>=', $filters['date']['start']);
        if (!empty($filters['date']['end'])) $query->whereDate('created_at', '<=', $filters['date']['end']);
        $sales = $query->latest()->get();

        // Generate the PDF for the selected report type
        $pdf = null;
        if ($reportType === 'sales') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.sales-report', [
                'sales' => $sales->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'business_id' => $sale->business_id,
                        'branch_id' => $sale->branch_id,
                        'seller_id' => $sale->seller_id,
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
                            $product = $item->product;
                            $inventoryItemName = null;
                            if ($product && $product->inventoryItem) {
                                $inventoryItemName = $product->inventoryItem->name;
                            }
                            return [
                                'id' => $item->id,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'total' => $item->total,
                                'product_name' => $inventoryItemName ?? $product->name ?? 'Unknown Product',
                                'product' => [
                                    'id' => $product->id ?? null,
                                    'name' => $product->name ?? null,
                                    'display_name' => $product->display_name ?? null,
                                    'buying_price' => $product->buying_price ?? null,
                                    'selling_price' => $product->selling_price ?? null,
                                    'barcode' => $product->barcode ?? null,
                                    'inventoryItem' => $product->inventoryItem ? [
                                        'id' => $product->inventoryItem->id,
                                        'name' => $product->inventoryItem->name,
                                        'description' => $product->inventoryItem->description
                                    ] : null
                                ]
                            ];
                        })
                    ];
                })
            ]);
        } elseif ($reportType === 'profit') {
            $profitData = $sales->map(function ($sale) {
                $profit = $sale->items->reduce(function ($sum, $item) {
                    $buy = $item->product->buying_price ?? 0;
                    $sell = $item->unit_price ?? 0;
                    return $sum + (($sell - $buy) * $item->quantity);
                }, 0);
                $products = $sale->items->map(function ($item) {
                    $productName = $item->product->inventoryItem->name ?? $item->product->name ?? $item->product_name ?? 'N/A';
                    return $productName . ' x' . $item->quantity .
                        ' (Buy: KES ' . $item->product->buying_price . ', Sell: KES ' . $item->unit_price . ', Profit: KES ' . number_format(($item->unit_price - $item->product->buying_price) * $item->quantity, 2) . ')';
                })->join('<br>');
                return [
                    'date' => $sale->created_at->format('Y-m-d'),
                    'business' => $sale->business->name ?? '',
                    'branch' => $sale->branch->name ?? '',
                    'seller' => $sale->seller->name ?? '',
                    'products' => $products,
                    'profit' => $profit,
                ];
            });
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.profit-report', [
                'profitData' => $profitData,
            ]);
        } elseif ($reportType === 'peritem') {
            $perItemData = SalesReceiptItem::select(
                    'sales_receipt_items.product_name',
                    \DB::raw('SUM(sales_receipt_items.quantity) as qty'),
                    'inventory_items.unit as unit'
                )
                ->leftJoin('products', 'sales_receipt_items.product_id', '=', 'products.id')
                ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
                ->groupBy('sales_receipt_items.product_name', 'inventory_items.unit')
                ->orderByDesc('qty')
                ->get()
                ->map(function ($item) {
                    return [
                        'product_name' => $item->product_name,
                        'qty' => $item->qty,
                        'unit' => $item->unit,
                    ];
                });
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.per-item-report', [
                'perItemData' => $perItemData,
            ]);
        } elseif ($reportType === 'items') {
            $itemsData = \DB::table('products')
                ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
                ->selectRaw('COALESCE(products.name, inventory_items.name) as name, products.stock')
                ->get();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.items-report', [
                'itemsData' => $itemsData,
            ]);
        } elseif ($reportType === 'stock') {
            $stockData = \DB::table('products')
                ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
                ->selectRaw('COALESCE(products.name, inventory_items.name) as name, products.stock')
                ->get();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.stock-report', [
                'stockData' => $stockData,
            ]);
        } elseif ($reportType === 'valuation') {
            $itemsValuationData = \DB::table('products')
                ->leftJoin('inventory_items', 'products.inventory_item_id', '=', 'inventory_items.id')
                ->selectRaw('COALESCE(products.name, inventory_items.name) as name, products.stock, products.buying_price, products.price')
                ->get();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.stock-item-valuation', [
                'itemsValuationData' => $itemsValuationData,
            ]);
        } elseif ($reportType === 'pl') {
            $plByPeriod = [];
            foreach ($sales as $sale) {
                switch ($granularity) {
                    case 'year':
                        $key = $sale->created_at->format('Y');
                        break;
                    case 'month':
                        $key = $sale->created_at->format('Y-m');
                        break;
                    case 'day':
                        $key = $sale->created_at->format('Y-m-d');
                        break;
                    case 'hour':
                        $key = $sale->created_at->format('Y-m-d H:00');
                        break;
                    default:
                        $key = $sale->created_at->format('Y-m');
                }
                $profit = $sale->items->reduce(function ($sum, $item) {
                    $buy = $item->product->buying_price ?? 0;
                    $sell = $item->unit_price ?? 0;
                    return $sum + (($sell - $buy) * $item->quantity);
                }, 0);
                if (!isset($plByPeriod[$key])) {
                    $plByPeriod[$key] = 0;
                }
                $plByPeriod[$key] += $profit;
            }
            $plData = collect($plByPeriod)->map(function ($profit, $period) {
                return [
                    'month' => $period,
                    'profit' => $profit,
                ];
            })->values();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.profit-loss-report', [
                'plData' => $plData,
                'granularity' => $granularity,
            ]);
        }
        if ($pdf) {
            $pdfContent = $pdf->output();
            \Mail::to($user->email)->send(new \App\Mail\ReportMail($pdfContent, $reportType));
        }
        return response()->json(['status' => 'sent']);
    }
} 