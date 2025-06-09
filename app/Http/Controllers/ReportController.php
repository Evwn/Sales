<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Business;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['products', 'branch.business', 'seller']);

        if (auth()->user()->role === 'seller') {
            $query->where('seller_id', auth()->id());
        } else {
            $query->whereHas('branch.business', function ($q) {
                $q->where('owner_id', auth()->id())
                    ->orWhereHas('admins', function ($query) {
                        $query->where('admin_id', auth()->id());
                    });
            });
        }

        // Apply filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('business_id')) {
            $query->whereHas('branch', function ($q) use ($request) {
                $q->where('business_id', $request->business_id);
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $sales = $query->latest()->paginate(10);

        // Calculate totals
        $totals = [
            'sales_count' => $sales->total(),
            'total_revenue' => $query->sum('total_amount'),
            'average_sale' => $sales->total() > 0 ? $query->avg('total_amount') : 0,
        ];

        // Get businesses for filter
        $businesses = [];
        if (auth()->user()->role === 'admin') {
            $businesses = Business::where('owner_id', auth()->id())
                ->orWhereHas('admins', function ($query) {
                    $query->where('admin_id', auth()->id());
                })
                ->get();
        }

        return Inertia::render('Reports/Index', [
            'reports' => [
                'total_sales' => $totals['sales_count'],
                'total_revenue' => $totals['total_revenue'],
                'recent_sales' => $sales->items(),
            ],
            'businesses' => $businesses,
            'filters' => $request->only(['start_date', 'end_date', 'business_id', 'branch_id']),
        ]);
    }

    public function export(Request $request)
    {
        $query = Sale::with(['products.business', 'branch', 'seller']);

        if (auth()->user()->role === 'seller') {
            $query->where('seller_id', auth()->id());
        } else {
            $query->whereHas('products.business', function ($q) {
                $q->where('owner_id', auth()->id())
                    ->orWhereHas('admins', function ($query) {
                        $query->where('admin_id', auth()->id());
                    });
            });
        }

        // Apply filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('business_id')) {
            $query->whereHas('products', function ($q) use ($request) {
                $q->where('business_id', $request->business_id);
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $sales = $query->latest()->get();

        // Generate CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=sales-report.csv',
        ];

        $callback = function () use ($sales) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Date',
                'Business',
                'Branch',
                'Products',
                'Total Amount',
                'Payment Method',
                'Seller',
            ]);

            foreach ($sales as $sale) {
                $products = $sale->products->map(function ($product) {
                    return $product->name . ' (' . $product->pivot->quantity . ' x $' . $product->pivot->price . ')';
                })->join(', ');

                fputcsv($file, [
                    $sale->created_at->format('Y-m-d H:i:s'),
                    $sale->products->first()->business->name,
                    $sale->branch->name,
                    $products,
                    $sale->total_amount,
                    $sale->payment_method,
                    $sale->seller->name,
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