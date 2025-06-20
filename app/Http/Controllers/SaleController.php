<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\SaleService;
use App\Models\SalesReceipt;
use App\Models\SalesReceiptItem;
use App\Services\BarcodeService;
use App\Services\PDFService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogger;

class SaleController extends Controller
{
    protected $saleService;
    protected $whatsappService;

    public function __construct(SaleService $saleService, WhatsAppService $whatsappService)
    {
        $this->saleService = $saleService;
        $this->whatsappService = $whatsappService;
    }

    public function index(Business $business, Branch $branch)
    {
        $query = Sale::where('business_id', $business->id);

        // If seller, only show their own sales
        if (Auth::user()->role->name === 'seller') {
            $query->where('branch_id', $branch->id)
                  ->where('seller_id', Auth::id());
        }

        $sales = $query->with([
            'seller',
            'branch.business',
            'items.product.inventoryItem'
        ])->get();

        Log::info('Print Receipt - Loaded sales data', [
            'total_sales' => $sales->count(),
            'first_sale' => $sales->first() ? [
                'id' => $sales->first()->id,
                'reference' => $sales->first()->reference,
                'business_id' => $sales->first()->business_id,
                'branch_id' => $sales->first()->branch_id,
                'items_count' => $sales->first()->items->count(),
                'has_seller' => $sales->first()->seller ? true : false,
                'has_business' => $sales->first()->branch->business ? true : false,
                'has_branch' => $sales->first()->branch ? true : false
            ] : null
        ]);

        return Inertia::render('Sales/Index', [
            'sales' => [
                'data' => $sales->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'business_id' => $sale->business_id,
                        'branch_id' => $sale->branch_id,
                        'reference' => $sale->reference,
                        'date' => $sale->sale_date->format('d/m/Y'),
                        'amount' => $sale->amount,
                        'seller' => $sale->seller->name,
                        'branch' => $sale->branch->name,
                        'business' => $sale->branch->business->name,
                        'payment_method' => $sale->payment_method,
                        'status' => $sale->status,
                        'items' => $sale->items->map(function ($item) {
                            return [
                                'product_name' => $item->product->inventoryItem->name,
                                'quantity' => $item->quantity,
                                'price' => $item->unit_price,
                                'total' => $item->total_price
                            ];
                        })
                    ];
                }),
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => $sales->count()
                ]
            ]
        ]);
    }

    public function create(Business $business, Branch $branch)
    {
        return view('sales.create', compact('business', 'branch'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'seller_id' => 'required|exists:users,id',
            'business_id' => 'required|exists:businesses,id',
            'branch_id' => 'required|exists:branches,id',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,mpesa',
            'items' => 'required|array',
            'items.*.product.id' => 'required|exists:products,id',
            'items.*.product.name' => 'required|string',
            'items.*.product.price' => 'required|numeric|min:0',
            'items.*.product.barcode' => 'required|string',
            'items.*.product.is_taxable' => 'required|boolean',
            'items.*.product.tax_rate' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        try {
            $result = $this->saleService->processSale($validated);
            // Log the activity
            ActivityLogger::logSaleCreated($result['sale'], auth()->user());

            // If the request expects JSON (e.g., AJAX/Inertia modal), return receipt data for printing
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'receipt' => $result['receipt']->load('items'),
                    'sale' => $result['sale'],
                ]);
            }

            // Otherwise, redirect to sales index with success message
            return redirect()->route('sales.index')->with('success', 'Sale processed successfully');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                'error' => 'Error processing sale: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error processing sale: ' . $e->getMessage());
        }
    }

    public function show(Business $business, Branch $branch, Sale $sale)
    {
        if ($sale->branch_id !== $branch->id) {
            abort(404);
        }

        // If seller, verify they own the sale
        if (Auth::user()->role->name === 'seller' && $sale->seller_id !== Auth::id()) {
            abort(403);
        }

        $sale->load([
            'seller',
            'items.product.inventoryItem',
            'branch.business',
            'customer'
        ]);

        return Inertia::render('Sales/Show', [
            'sale' => [
                'id' => $sale->id,
                'reference' => $sale->reference,
                'created_at' => $sale->created_at,
                'total_amount' => $sale->total_amount,
                'payment_method' => $sale->payment_method,
                'status' => $sale->status,
                'seller' => [
                    'id' => $sale->seller->id,
                    'name' => $sale->seller->name
                ],
                'branch' => [
                    'id' => $sale->branch->id,
                    'name' => $sale->branch->name,
                    'business' => [
                        'id' => $sale->branch->business->id,
                        'name' => $sale->branch->business->name
                    ]
                ],
                'items' => $sale->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->inventoryItem->name,
                            'barcode' => $item->product->inventoryItem->barcode
                        ],
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price
                    ];
                })
            ]
        ]);
    }

    public function edit(Business $business, Branch $branch, Sale $sale)
    {
        if ($sale->branch_id !== $branch->id) {
            abort(404);
        }

        // Only allow editing recent sales (within 24 hours)
        if ($sale->created_at->diffInHours(now()) > 24) {
            abort(403, 'Sales can only be edited within 24 hours of creation.');
        }

        // If seller, verify they own the sale
        if (Auth::user()->role->name === 'seller' && $sale->seller_id !== Auth::id()) {
            abort(403);
        }

        $sale->load(['products']);

        return view('sales.edit', compact('business', 'branch', 'sale'));
    }

    public function update(Request $request, Business $business, Branch $branch, Sale $sale)
    {
        if ($sale->branch_id !== $branch->id) {
            abort(404);
        }

        // Only allow editing recent sales (within 24 hours)
        if ($sale->created_at->diffInHours(now()) > 24) {
            abort(403, 'Sales can only be edited within 24 hours of creation.');
        }

        // If seller, verify they own the sale
        if (Auth::user()->role->name === 'seller' && $sale->seller_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card',
            'notes' => 'nullable|string',
        ]);

        $sale->update([
            'total_amount' => $validated['total_amount'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Sync products
        $sale->products()->sync(collect($validated['products'])->mapWithKeys(function ($product) {
            return [$product['id'] => [
                'quantity' => $product['quantity'],
                'price' => $product['price'],
            ]];
        }));

        return redirect()->route('sales.show', [$business, $branch, $sale])
            ->with('success', 'Sale updated successfully.');
    }

    public function destroy(Business $business, Branch $branch, Sale $sale)
    {
        if ($sale->branch_id !== $branch->id) {
            abort(404);
        }

        // Only allow deleting recent sales (within 24 hours)
        if ($sale->created_at->diffInHours(now()) > 24) {
            abort(403, 'Sales can only be deleted within 24 hours of creation.');
        }

        // If seller, verify they own the sale
        if (Auth::user()->role->name === 'seller' && $sale->seller_id !== Auth::id()) {
            abort(403);
        }

        $sale->products()->detach();
        $sale->delete();

        return redirect()->route('sales.index', [$business, $branch])
            ->with('success', 'Sale deleted successfully.');
    }

    public function all()
    {
        $user = auth()->user();
        $query = Sale::with([
            'seller',
            'branch.business',
            'customer',
            'items.product.inventoryItem',
            'sales_receipts.items'
        ]);

        // Use Spatie's hasRole for seller check
        if ($user->hasRole('seller')) {
            $query->where('seller_id', $user->id);
        }

        $sales = $query->latest('sale_date')->paginate(10);

        return Inertia::render('Sales/Index', [
            'sales' => [
                'data' => collect($sales->items())->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'reference' => $sale->reference,
                        'created_at' => $sale->created_at,
                        'sale_date' => $sale->sale_date,
                        'amount' => $sale->amount,
                        'discount' => $sale->discount,
                        'tax' => $sale->tax,
                        'status' => $sale->status,
                        'payment_status' => $sale->payment_status,
                        'payment_method' => $sale->payment_method,
                        'seller' => $sale->seller ? [
                            'id' => $sale->seller->id,
                            'name' => $sale->seller->name
                        ] : null,
                        'customer' => $sale->customer ? [
                            'id' => $sale->customer->id,
                            'name' => $sale->customer->name
                        ] : null,
                        'branch' => $sale->branch ? [
                            'id' => $sale->branch->id,
                            'name' => $sale->branch->name,
                            'business' => $sale->branch->business ? [
                                'id' => $sale->branch->business->id,
                                'name' => $sale->branch->business->name
                            ] : null
                        ] : null,
                        'sales_receipts' => $sale->sales_receipts->map(function ($receipt) {
                            return [
                                'id' => $receipt->id,
                                'reference' => $receipt->reference,
                                'subtotal' => $receipt->subtotal,
                                'discount' => $receipt->discount,
                                'tax' => $receipt->tax,
                                'total' => $receipt->total,
                                'total_quantity' => $receipt->total_quantity,
                                'payment_methods' => $receipt->payment_methods,
                                'notes' => $receipt->notes,
                                'items' => $receipt->items->map(function ($item) {
                                    return [
                                        'product_name' => $item->product_name,
                                        'quantity' => $item->quantity,
                                        'unit_price' => $item->unit_price,
                                        'subtotal' => $item->subtotal,
                                        'discount' => $item->discount,
                                        'tax' => $item->tax,
                                        'total' => $item->total
                                    ];
                                })
                            ];
                        }),
                        'items' => $sale->items->map(function ($item) {
                            return [
                                'product_name' => $item->product->inventoryItem->name,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'total_price' => $item->total_price
                            ];
                        })
                    ];
                }),
                'meta' => [
                    'current_page' => $sales->currentPage(),
                    'last_page' => $sales->lastPage(),
                    'per_page' => $sales->perPage(),
                    'total' => $sales->total()
                ]
            ]
        ]);
    }

    /**
     * Verify a barcode and return the associated sale or receipt information
     */
    public function verifyBarcode(Request $request)
    {
        $barcode = $request->input('barcode');
        
        // Check if it's a sale barcode
        if (str_starts_with($barcode, 'SALE-')) {
            $sale = Sale::with(['customer', 'seller', 'business', 'branch', 'items.product'])
                ->where('barcode', $barcode)
                ->first();
            
            if ($sale) {
                return response()->json([
                    'type' => 'sale',
                    'data' => $sale
                ]);
            }
        }
        
        // Check if it's a receipt barcode
        if (str_starts_with($barcode, 'RECEIPT-')) {
            $receipt = SalesReceipt::with(['sale', 'customer', 'cashier', 'business', 'branch', 'items.product'])
                ->where('barcode', $barcode)
                ->first();
            
            if ($receipt) {
                return response()->json([
                    'type' => 'receipt',
                    'data' => $receipt
                ]);
            }
        }
        
        // Check if it's a receipt item barcode
        if (str_starts_with($barcode, 'ITEM-')) {
            $item = SalesReceiptItem::with(['salesReceipt.sale', 'product'])
                ->where('barcode', $barcode)
                ->first();
            
            if ($item) {
                return response()->json([
                    'type' => 'receipt_item',
                    'data' => $item
                ]);
            }
        }
        
        return response()->json([
            'error' => 'Invalid or expired barcode'
        ], 404);
    }

    /**
     * Print a sale receipt
     */
    public function printReceipt(Sale $sale)
    {
        Log::info('Print Receipt - Starting process', [
            'sale_id' => $sale->id,
            'reference' => $sale->reference,
            'business_id' => $sale->business_id,
            'branch_id' => $sale->branch_id
        ]);

        $sale->load(['customer', 'seller', 'business', 'branch', 'items.product']);

        Log::info('Print Receipt - Loaded sale data', [
            'sale' => $sale->toArray(),
            'items_count' => $sale->items->count(),
            'has_customer' => $sale->customer ? true : false,
            'has_seller' => $sale->seller ? true : false,
            'has_business' => $sale->business ? true : false,
            'has_branch' => $sale->branch ? true : false
        ]);

        return Inertia::render('Sales/PrintReceipt', [
            'sale' => $sale
        ]);
    }

    public function receiptHistory(Request $request)
    {
        $query = Sale::with(['branch', 'seller', 'items.product'])
            ->where('business_id', auth()->user()->business_id);

        // Apply search filters
        if ($request->filled('reference')) {
            $query->where('reference', 'like', '%' . $request->reference . '%');
        }

        if ($request->filled('barcode')) {
            $query->where('barcode', 'like', '%' . $request->barcode . '%');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $receipts = $query->latest()->paginate(10);

        return Inertia::render('Sales/ReceiptHistory', [
            'receipts' => $receipts
        ]);
    }

    public function exportReceiptPDF(Request $request, Sale $sale)
    {
        $pdf = $this->pdfService->generateReceiptPDF($sale);
        
        if ($request->has('whatsapp')) {
            $whatsappService = app(WhatsAppService::class);
            $whatsappService->sendPDF(
                $request->whatsapp_number,
                $pdf->output(),
                "Receipt for sale #{$sale->reference}"
            );
            return response()->json(['message' => 'Receipt sent via WhatsApp']);
        }
        
        return $pdf->download("receipt-{$sale->reference}.pdf");
    }

    public function exportMultipleReceiptsPDF(Request $request)
    {
        $request->validate([
            'sale_ids' => 'required|array',
            'sale_ids.*' => 'exists:sales,id'
        ]);

        $sales = Sale::whereIn('id', $request->sale_ids)->get();
        $pdfService = app(PDFService::class);
        $pdf = $pdfService->generateMultipleReceiptsPDF($sales);
        
        return $pdf->download("receipts-{$sales->first()->business->id}.pdf");
    }

    public function exportSalesReportPDF(Request $request)
    {
        $pdf = $this->pdfService->generateSalesReportPDF(
            $request->user()->business,
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('branch_id')
        );
        
        if ($request->has('whatsapp')) {
            $whatsappService = app(WhatsAppService::class);
            $whatsappService->sendPDF(
                $request->whatsapp_number,
                $pdf->output(),
                "Sales Report for period {$request->input('start_date')} to {$request->input('end_date')}"
            );
            return response()->json(['message' => 'Report sent via WhatsApp']);
        }
        
        return $pdf->download('sales-report.pdf');
    }

    public function exportDailyReportPDF(Request $request)
    {
        $pdf = $this->pdfService->generateDailyReportPDF(
            $request->user()->business,
            $request->input('date'),
            $request->input('branch_id')
        );
        
        if ($request->has('whatsapp')) {
            $whatsappService = app(WhatsAppService::class);
            $whatsappService->sendPDF(
                $request->whatsapp_number,
                $pdf->output(),
                "Daily Report for {$request->input('date')}"
            );
            return response()->json(['message' => 'Report sent via WhatsApp']);
        }
        
        return $pdf->download('daily-report.pdf');
    }

    public function exportWeeklyReportPDF(Request $request)
    {
        $pdf = $this->pdfService->generateWeeklyReportPDF(
            $request->user()->business,
            $request->input('week'),
            $request->input('year'),
            $request->input('branch_id')
        );
        
        if ($request->has('whatsapp')) {
            $whatsappService = app(WhatsAppService::class);
            $whatsappService->sendPDF(
                $request->whatsapp_number,
                $pdf->output(),
                "Weekly Report for Week {$request->input('week')}, {$request->input('year')}"
            );
            return response()->json(['message' => 'Report sent via WhatsApp']);
        }
        
        return $pdf->download('weekly-report.pdf');
    }

    public function exportMonthlyReportPDF(Request $request)
    {
        $pdf = $this->pdfService->generateMonthlyReportPDF(
            $request->user()->business,
            $request->input('month'),
            $request->input('year'),
            $request->input('branch_id')
        );
        
        if ($request->has('whatsapp')) {
            $whatsappService = app(WhatsAppService::class);
            $whatsappService->sendPDF(
                $request->whatsapp_number,
                $pdf->output(),
                "Monthly Report for {$request->input('month')} {$request->input('year')}"
            );
            return response()->json(['message' => 'Report sent via WhatsApp']);
        }
        
        return $pdf->download('monthly-report.pdf');
    }

    public function exportQuarterlyReportPDF(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'quarter' => 'nullable|integer|min:1|max:4',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        $business = auth()->user()->business;
        $pdfService = app(PDFService::class);
        $pdf = $pdfService->generateQuarterlyReportPDF(
            $business,
            $request->year,
            $request->quarter,
            $request->branch_id
        );
        
        $filename = "quarterly-report-{$business->id}";
        if ($request->year && $request->quarter) {
            $filename .= "-Q{$request->quarter}-{$request->year}";
        }
        
        return $pdf->download("{$filename}.pdf");
    }

    public function exportYearlyReportPDF(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        $business = auth()->user()->business;
        $pdfService = app(PDFService::class);
        $pdf = $pdfService->generateYearlyReportPDF(
            $business,
            $request->year,
            $request->branch_id
        );
        
        $filename = "yearly-report-{$business->id}";
        if ($request->year) {
            $filename .= "-{$request->year}";
        }
        
        return $pdf->download("{$filename}.pdf");
    }

    public function exportBatchReceiptsPDF(Request $request)
    {
        $request->validate([
            'sale_ids' => 'required|array',
            'sale_ids.*' => 'exists:sales,id',
            'whatsapp_number' => 'nullable|string',
            'whatsapp' => 'nullable|boolean'
        ]);

        $sales = Sale::whereIn('id', $request->sale_ids)->get();
        $pdf = $this->pdfService->generateBatchReceiptsPDF($sales);
        
        if ($request->has('whatsapp')) {
            $whatsappService = app(WhatsAppService::class);
            $whatsappService->sendPDF(
                $request->whatsapp_number,
                $pdf->output(),
                "Batch Receipts for " . count($sales) . " sales"
            );
            return response()->json(['message' => 'Receipts sent via WhatsApp']);
        }
        
        return $pdf->download('batch-receipts.pdf');
    }

    // Public receipt by reference (no auth required)
    public function publicReceipt($reference)
    {
        \Log::info('Public receipt route hit', ['reference' => $reference]);

        // Find the receipt by reference
        $receipt = \App\Models\SalesReceipt::where('reference', $reference)
            ->with([
                'sale.seller',
                'sale.items.product.inventoryItem',
                'sale.branch.business',
                'sale.customer',
                'items'
            ])->firstOrFail();

        $sale = $receipt->sale;

        return Inertia::render('Sales/PublicReceipt', [
            'sale' => [
                'id' => $sale->id,
                'reference' => $sale->reference,
                'created_at' => $sale->created_at,
                'total_amount' => $sale->total_amount,
                'payment_method' => $sale->payment_method,
                'status' => $sale->status,
                'seller' => $sale->seller ? [
                    'id' => $sale->seller->id,
                    'name' => $sale->seller->name
                ] : null,
                'branch' => [
                    'id' => $sale->branch->id,
                    'name' => $sale->branch->name,
                    'business' => [
                        'id' => $sale->branch->business->id,
                        'name' => $sale->branch->business->name,
                        'logo_url' => $sale->branch->business->logo_url,
                        'receipt_footer' => $sale->branch->business->receipt_footer,
                        'terms_and_conditions' => $sale->branch->business->terms_and_conditions,
                        'contact_information' => $sale->branch->business->contact_information,
                    ]
                ],
                'items' => $sale->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->inventoryItem->name,
                            'barcode' => $item->product->inventoryItem->barcode
                        ],
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price
                    ];
                }),
                'customer' => $sale->customer ? [
                    'id' => $sale->customer->id,
                    'name' => $sale->customer->name
                ] : null,
                'barcode' => $sale->barcode,
                'subtotal' => $sale->total_amount, // You can adjust this if you have a separate subtotal
                // Add receipt-specific data if needed
                'receipt_reference' => $receipt->reference,
                'receipt_total' => $receipt->total,
                'receipt_items' => $receipt->items,
            ]
        ]);
    }
}