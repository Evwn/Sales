<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Item;
use App\Models\Business;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\StockItem;
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
            $userBusinesses = Business::pluck('id');
            $sales = Sale::with(['business', 'branch', 'seller', 'items.stockItem.item'])->latest()->get();
            $businesses = Business::all(['id', 'name']);
            $branches = \App\Models\Branch::all(['id', 'name', 'business_id']);
            $sellers = User::role('cashier')->with(['business', 'branch'])->get(['id', 'name', 'business_id', 'branch_id']);
        } else {
            $userBusinesses = Business::where('owner_id', $user->id)
                ->orWhereHas('admins', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->pluck('id');

            $sales = Sale::whereIn('business_id', $userBusinesses)
                ->with(['business', 'branch', 'seller', 'items.stockItem.item'])
                ->latest()
                ->get();

            $businesses = Business::whereIn('id', $userBusinesses)->get(['id', 'name']);
            $branches = \App\Models\Branch::whereIn('business_id', $userBusinesses)->get(['id', 'name', 'business_id']);
            $sellers = User::role('cashier')->whereIn('business_id', $userBusinesses)->with(['business', 'branch'])->get(['id', 'name', 'business_id', 'branch_id']);
        }

        // Summary calculations
        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('amount');

        // Top products (items)
        $topProductsQuery = \DB::table('sale_items')
            ->join('stock_items', 'sale_items.stock_item_id', '=', 'stock_items.id')
            ->join('items', 'stock_items.item_id', '=', 'items.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereIn('sales.business_id', $userBusinesses)
            ->select('items.id', 'items.name', \DB::raw('SUM(sale_items.quantity) as qty'))
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // Top sellers
        $topSellersQuery = Sale::whereIn('business_id', $userBusinesses)
            ->select('seller_id', \DB::raw('COUNT(*) as sales_count'))
            ->groupBy('seller_id')
            ->orderByDesc('sales_count')
            ->with('seller')
            ->limit(5)
            ->get();

        // Sales by business
        $salesByBusiness = Sale::whereIn('business_id', $userBusinesses)
            ->select('business_id', \DB::raw('SUM(amount) as revenue'))
            ->groupBy('business_id')
            ->with('business')
            ->get();

        // Map top products & sellers
        $topProducts = $topProductsQuery->map(fn($item) => ($item->name ?? 'Unknown Item') . ' (' . $item->qty . ' sold)');
        $topSellers = $topSellersQuery->map(fn($item) => ($item->seller->name ?? 'Unknown Seller') . ' (' . $item->sales_count . ' sales)');

        // Per Item Sales from sales_receipt_items
        $perItemSalesData = SalesReceiptItem::select(
                'items.name as product_name',
                \DB::raw('SUM(sales_receipt_items.quantity) as qty'),
                'items.unit_id as unit'
            )
            ->join('stock_items', 'sales_receipt_items.stock_item_id', '=', 'stock_items.id')
            ->join('items', 'stock_items.item_id', '=', 'items.id')
            ->groupBy('items.name', 'items.unit_id')
            ->orderByDesc('qty')
            ->get();

        // Items Report (Stock)
        $itemsReportData = \DB::table('stock_items')
            ->join('items', 'stock_items.item_id', '=', 'items.id')
            ->select('items.name as name',
                    'stock_items.quantity as stock')
            ->get();

        // Stock & Item Valuation
        $itemsValuationData = \DB::table('stock_items')
            ->join('items', 'stock_items.item_id', '=', 'items.id')
            ->select(
                        'items.name as name',
                        'stock_items.quantity as stock',
                        'stock_items.cost as buying_price',
                        'stock_items.price'
            )
            ->get();

        // Products filter (from stock_items via sales_receipt_items)
        $products = \DB::table('sales_receipt_items')
            ->join('stock_items', 'sales_receipt_items.stock_item_id', '=', 'stock_items.id')
            ->join('branches', 'stock_items.location_id', '=', 'branches.id')
            ->join('items', 'stock_items.item_id', '=', 'items.id')
            ->select(
                'stock_items.id as id',
                \DB::raw("CONCAT(items.name, ' (', branches.name, ')') as name"),
                'stock_items.location_id',
                'branches.business_id'
            )
            ->groupBy('stock_items.id', 'items.name', 'branches.name', 'stock_items.location_id', 'branches.business_id')
            ->orderBy('items.name')
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
                        $stockItem = $item->stockItem;
                        $inventoryItem = $stockItem?->item;
                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'unit_price' => $stockItem->price,
                            'total' => ($item->quantity * $item->unit_price),
                            'product_name' => $inventoryItem->name ?? 'Unknown Item',
                            'product' => [
                                'id' => $stockItem->id ?? null,
                                'name' => $inventoryItem->name ?? null,
                                'display_name' => $stockItem->display_name ?? null,
                                'buying_price' => $stockItem->cost ?? null,
                                'selling_price' => $stockItem->price ?? null,
                                'barcode' => $stockItem->barcode ?? null,
                                'inventoryItem' => $inventoryItem ? [
                                    'id' => $inventoryItem->id,
                                    'name' => $inventoryItem->name,
                                    'description' => $inventoryItem->description
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
        $userBusinesses = Business::where('owner_id', $user->id)
            ->orWhereHas('admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');
                
        $query = Sale::query()
            ->whereIn('business_id', $userBusinesses)
            ->with(['business', 'branch', 'seller', 'items.stockItem.item'])
            ->when($request->business_id, fn($q) => $q->where('business_id', $request->business_id))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->seller_id, fn($q) => $q->where('seller_id', $request->seller_id))
            ->when($request->product_id, function ($q) use ($request) {
                $q->whereHas('items.stockItem', fn($q2) => $q2->where('stock_item_id', $request->product_id));
            })
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to));

        $sales = $query->latest()->get();

        $reportType = $request->get('report_type', 'sales');

        // PDF export for each report type
        if ($request->format === 'pdf') {
            if ($reportType === 'valuation') {
                $itemsValuationData = \DB::table('stock_items')
                    ->join('items', 'stock_items.item_id', '=', 'items.id')
                    ->select(
                        'items.name as name',
                        'stock_items.quantity as stock',
                        'stock_items.cost as buying_price',
                        'stock_items.price'
                    )
                    ->get();

                $pdf = Pdf::loadView('pdfs.stock-item-valuation', [
                    'itemsValuationData' => $itemsValuationData,
                ]);
                return $pdf->download('stock-item-valuation.pdf');
            }

                if ($reportType === 'profit') {
                    $profitData = $sales->map(function ($sale) {
                        $profit = $sale->items->reduce(function ($sum, $item) {
                            $buy = $item->stockItem->cost ?? 0;
                            $sell = $item->unit_price ?? 0;
                            return $sum + (($sell - $buy) * $item->quantity);
                        }, 0);

                        $products = $sale->items->map(function ($item) {
                            $itemName = $item->stockItem->item->name ?? 'N/A';
                            $buy = $item->stockItem->cost ?? 0;
                            $sell = $item->unit_price ?? 0;
                            $profit = ($sell - $buy) * $item->quantity;

                            return $itemName . ' x' . $item->quantity .
                                ' (Buy: KES ' . $buy . ', Sell: KES ' . $sell . ', Profit: KES ' . number_format($profit, 2) . ')';
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
                        'items.name as product_name',
                        \DB::raw('SUM(sales_receipt_items.quantity) as qty'),
                        'items.unit_id as unit'
                    )
                    ->join('stock_items', 'sales_receipt_items.stock_item_id', '=', 'stock_items.id')
                    ->join('items', 'stock_items.item_id', '=', 'items.id')
                    ->groupBy('items.name', 'items.unit_id')
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
                $itemsData = \DB::table('stock_items')
                    ->join('items', 'stock_items.item_id', '=', 'items.id')
                    ->select(
                        'items.name as name',
                        'stock_items.quantity as stock'
                    )
                    ->get();

                $pdf = Pdf::loadView('pdfs.items-report', [
                    'itemsData' => $itemsData,
                ]);
                return $pdf->download('items-report.pdf');
            }

            if ($reportType === 'stock') {
                $stockData = \DB::table('stock_items')
                    ->join('items', 'stock_items.item_id', '=', 'items.id')
                    ->select(
                        'items.name as name',
                        'stock_items.quantity as stock'
                    )
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

                    // Calculate profit using stock_items cost & price
                    $profit = $sale->items->reduce(function ($sum, $item) {
                        $stockItem = $item->stockItem; 
                        $buy = $stockItem->cost ?? 0;
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
            if ($request->format === 'csv') {
                if ($reportType === 'valuation') {
                    $itemsValuationData = \DB::table('stock_items')
                        ->join('items', 'stock_items.item_id', '=', 'items.id')
                        ->select('items.name', 'stock_items.quantity as stock', 'stock_items.cost as buying_price', 'stock_items.price')
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
                            $buy = $item->stockItem->cost ?? 0;
                            $sell = $item->unit_price ?? 0;
                            return $sum + (($sell - $buy) * $item->quantity);
                        }, 0);

                        $products = $sale->items->map(function ($item) {
                            $productName = $item->stockItem->item->name ?? 'N/A';
                            $buy = $item->stockItem->cost ?? 0;
                            $sell = $item->unit_price ?? 0;
                            return $productName . ' x' . $item->quantity .
                                ' (Buy: KES ' . $buy . ', Sell: KES ' . $sell . ', Profit: KES ' . number_format(($sell - $buy) * $item->quantity, 2) . ')';
                        })->join(', ');

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
                            'items.name as product_name',
                            \DB::raw('SUM(sales_receipt_items.quantity) as qty'),
                            'items.unit_id as unit'
                        )
                        ->join('stock_items', 'sales_receipt_items.stock_item_id', '=', 'stock_items.id')
                        ->join('items', 'stock_items.item_id', '=', 'items.id')
                        ->groupBy('items.name', 'items.unit_id')
                        ->orderByDesc('qty')
                        ->get();

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
                    $itemsData = \DB::table('stock_items')
                        ->join('items', 'stock_items.item_id', '=', 'items.id')
                        ->select('items.name', 'stock_items.quantity as stock')
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
                    $stockData = \DB::table('stock_items')
                        ->join('items', 'stock_items.item_id', '=', 'items.id')
                        ->select('items.name', 'stock_items.quantity as stock')
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
                    $granularity = $request->input('pl_granularity', 'month');
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
                            $buy = $item->stockItem->cost ?? 0;
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

                    $headers = [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => 'attachment; filename=profit-loss-report.csv',
                    ];

                    $callback = function () use ($plData) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, ['Period', 'Profit']);
                        foreach ($plData as $row) {
                            fputcsv($file, [
                                $row['month'],
                                $row['profit'],
                            ]);
                        }
                        fclose($file);
                    };
                    return response()->stream($callback, 200, $headers);
                }

                // Default sales CSV export
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename=sales-report.csv',
                ];

                $callback = function () use ($sales) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, ['Date', 'Business', 'Branch', 'Products', 'Total Amount', 'Payment Method', 'Seller']);

                    foreach ($sales as $sale) {
                        $products = $sale->items->map(function ($item) {
                            $productName = $item->stockItem->item->name ?? 'Unknown Product';
                            $buy = $item->stockItem->cost ?? 0;
                            $sell = $item->unit_price ?? 0;
                            $profit = ($sell - $buy) * $item->quantity;
                            return $productName . ' (' . $item->quantity . ' x KES ' . $sell . ', Buy: KES ' . $buy . ', Profit: KES ' . number_format($profit, 2) . ')';
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

        // Build sales query
        $query = Sale::query()
            ->whereIn('business_id', Business::where('owner_id', $user->id)
                ->orWhereHas('admins', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->pluck('id'))
            ->with(['business', 'branch', 'seller', 'items.stockItem.item']);

        if (!empty($filters['business_id'])) $query->where('business_id', $filters['business_id']);
        if (!empty($filters['branch_id'])) $query->where('branch_id', $filters['branch_id']);
        if (!empty($filters['seller_id'])) $query->where('seller_id', $filters['seller_id']);
        if (!empty($filters['product_id'])) {
            $query->whereHas('items', fn($q2) => $q2->where('stock_item_id', $filters['product_id']));
        }
        if (!empty($filters['date']['start'])) $query->whereDate('created_at', '>=', $filters['date']['start']);
        if (!empty($filters['date']['end'])) $query->whereDate('created_at', '<=', $filters['date']['end']);

        $sales = $query->latest()->get();

        $pdf = null;

        // SALES REPORT
        if ($reportType === 'sales') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.sales-report', [
                'sales' => $sales->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'business' => $sale->business ? ['id' => $sale->business->id, 'name' => $sale->business->name] : null,
                        'branch' => $sale->branch ? ['id' => $sale->branch->id, 'name' => $sale->branch->name] : null,
                        'seller' => $sale->seller ? ['id' => $sale->seller->id, 'name' => $sale->seller->name] : null,
                        'reference' => $sale->reference,
                        'created_at' => $sale->created_at,
                        'amount' => $sale->amount,
                        'payment_method' => $sale->payment_method,
                        'status' => $sale->status,
                        'items' => $sale->items->map(function ($item) {
                            $stockItem = $item->stockItem;
                            $itemData = $stockItem ? $stockItem->item : null;
                            return [
                                'id' => $item->id,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'total' => $item->total,
                                'product_name' => $itemData->name ?? 'Unknown Product',
                                'product' => $itemData ? [
                                    'id' => $itemData->id,
                                    'name' => $itemData->name,
                                    'buying_price' => $stockItem->cost,
                                    'selling_price' => $stockItem->price,
                                    'barcode' => $itemData->barcode ?? null,
                                    'description' => $itemData->description ?? null
                                ] : null
                            ];
                        })
                    ];
                })
            ]);
        }

        // PROFIT REPORT
        elseif ($reportType === 'profit') {
            $profitData = $sales->map(function ($sale) {
                $profit = $sale->items->reduce(function ($sum, $item) {
                    $buy = $item->stockItem->cost ?? 0;
                    $sell = $item->unit_price ?? 0;
                    return $sum + (($sell - $buy) * $item->quantity);
                }, 0);
                $products = $sale->items->map(function ($item) {
                    $stockItem = $item->stockItem;
                    $product = $stockItem?->item;
                    return ($product->name ?? 'N/A') . ' x' . $item->quantity .
                        ' (Buy: KES ' . $stockItem->cost . ', Sell: KES ' . $item->unit_price . ', Profit: KES ' .
                        number_format(($item->unit_price - $stockItem->cost) * $item->quantity, 2) . ')';
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

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.profit-report', ['profitData' => $profitData]);
        }

        // PER ITEM REPORT
        elseif ($reportType === 'peritem') {
            $perItemData = SalesReceiptItem::select(
                    'items.name as product_name',
                    \DB::raw('SUM(sales_receipt_items.quantity) as qty'),
                    'items.unit_id as unit'
                )
                ->join('stock_items', 'sales_receipt_items.stock_item_id', '=', 'stock_items.id')
                ->join('items', 'stock_items.item_id', '=', 'items.id')
                ->groupBy('items.name', 'items.unit_id')
                ->orderByDesc('qty')
                ->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.per-item-report', ['perItemData' => $perItemData]);
        }

        // ITEMS REPORT
        elseif ($reportType === 'items') {
            $itemsData = \DB::table('stock_items')
                ->join('items', 'stock_items.item_id', '=', 'items.id')
                ->select('items.name', 'stock_items.quantity as stock')
                ->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.items-report', ['itemsData' => $itemsData]);
        }

        // STOCK REPORT
        elseif ($reportType === 'stock') {
            $stockData = \DB::table('stock_items')
                ->join('items', 'stock_items.item_id', '=', 'items.id')
                ->select('items.name', 'stock_items.quantity as stock')
                ->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.stock-report', ['stockData' => $stockData]);
        }

        // VALUATION REPORT
        elseif ($reportType === 'valuation') {
            $itemsValuationData = \DB::table('stock_items')
                ->join('items', 'stock_items.item_id', '=', 'items.id')
                ->select('items.name', 'stock_items.quantity as stock', 'stock_items.cost as buying_price', 'stock_items.price')
                ->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.stock-item-valuation', ['itemsValuationData' => $itemsValuationData]);
        }

        // PROFIT & LOSS REPORT
        elseif ($reportType === 'pl') {
            $plByPeriod = [];
            foreach ($sales as $sale) {
                switch ($granularity) {
                    case 'year': $key = $sale->created_at->format('Y'); break;
                    case 'month': $key = $sale->created_at->format('Y-m'); break;
                    case 'day': $key = $sale->created_at->format('Y-m-d'); break;
                    case 'hour': $key = $sale->created_at->format('Y-m-d H:00'); break;
                    default: $key = $sale->created_at->format('Y-m');
                }
                $profit = $sale->items->reduce(function ($sum, $item) {
                    $buy = $item->stockItem->cost ?? 0;
                    $sell = $item->unit_price ?? 0;
                    return $sum + (($sell - $buy) * $item->quantity);
                }, 0);
                $plByPeriod[$key] = ($plByPeriod[$key] ?? 0) + $profit;
            }

            $plData = collect($plByPeriod)->map(fn($profit, $period) => ['month' => $period, 'profit' => $profit])->values();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.profit-loss-report', ['plData' => $plData, 'granularity' => $granularity]);
        }

        // Send Email
        if ($pdf) {
            $pdfContent = $pdf->output();
            \Mail::to($user->email)->send(new \App\Mail\ReportMail($pdfContent, $reportType));
        }

        return response()->json(['status' => 'sent']);
    }
    

} 