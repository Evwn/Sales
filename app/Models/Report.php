<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'start_date',
        'end_date',
        'business_id',
        'branch_id',
        'created_by',
        'parameters',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'parameters' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generateSalesReport()
    {
        return Sale::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->with(['customer', 'seller', 'saleItems.product'])
            ->get();
    }

    public function generatePurchaseReport()
    {
        return Purchase::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->with(['supplier', 'purchaseItems.product'])
            ->get();
    }

    public function generateProfitLossReport()
    {
        $sales = Sale::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->sum('total_amount');

        $purchases = Purchase::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->sum('total_amount');

        $expenses = Transaction::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->where('type', 'debit')
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->sum('amount');

        return [
            'revenue' => $sales,
            'cost_of_goods' => $purchases,
            'expenses' => $expenses,
            'gross_profit' => $sales - $purchases,
            'net_profit' => $sales - $purchases - $expenses,
        ];
    }

    public function generateBalanceSheet()
    {
        $assets = Account::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->where('type', 'asset')
            ->sum('balance');

        $liabilities = Account::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->where('type', 'liability')
            ->sum('balance');

        $equity = Account::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->where('type', 'equity')
            ->sum('balance');

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_liabilities_equity' => $liabilities + $equity,
        ];
    }

    public function generateStockValuationReport()
    {
        return Product::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->with(['category', 'unit'])
            ->get()
            ->map(function ($product) {
                return [
                    'product' => $product->name,
                    'category' => $product->category->name,
                    'current_stock' => $product->current_stock,
                    'unit' => $product->unit->name,
                    'cost_price' => $product->purchase_price,
                    'selling_price' => $product->selling_price,
                    'total_value' => $product->current_stock * $product->purchase_price,
                ];
            });
    }

    public function generateStockAlertReport()
    {
        return Product::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereRaw('current_stock <= min_stock')
            ->with(['category', 'unit'])
            ->get()
            ->map(function ($product) {
                return [
                    'product' => $product->name,
                    'category' => $product->category->name,
                    'current_stock' => $product->current_stock,
                    'min_stock' => $product->min_stock,
                    'unit' => $product->unit->name,
                    'status' => $product->current_stock <= $product->min_stock ? 'Low Stock' : 'OK',
                ];
            });
    }

    public function generateCashFlowReport()
    {
        $transactions = Transaction::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->with('account')
            ->get();

        $mpesaTransactions = MpesaTransaction::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->get();

        return [
            'bank_transactions' => $transactions,
            'mpesa_transactions' => $mpesaTransactions,
            'total_inflow' => $transactions->where('type', 'credit')->sum('amount'),
            'total_outflow' => $transactions->where('type', 'debit')->sum('amount'),
            'net_cash_flow' => $transactions->where('type', 'credit')->sum('amount') - $transactions->where('type', 'debit')->sum('amount'),
        ];
    }

    public function generateCustomerSupplierReport()
    {
        $customers = Customer::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->with(['sales', 'invoices'])
            ->get();

        $suppliers = Supplier::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->with(['purchases', 'invoices'])
            ->get();

        return [
            'customers' => $customers,
            'suppliers' => $suppliers,
        ];
    }

    public function generateStockLedgerReport()
    {
        return Product::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->with([
                'sales' => fn($q) => $q->whereBetween('created_at', [$this->start_date, $this->end_date]),
                'purchases' => fn($q) => $q->whereBetween('created_at', [$this->start_date, $this->end_date]),
            ])
            ->get()
            ->map(function ($product) {
                return [
                    'product' => $product->name,
                    'opening_stock' => $product->current_stock,
                    'sales' => $product->sales->sum('pivot.quantity'),
                    'purchases' => $product->purchases->sum('pivot.quantity'),
                    'closing_stock' => $product->current_stock + $product->purchases->sum('pivot.quantity') - $product->sales->sum('pivot.quantity'),
                ];
            });
    }

    public function generateClearanceReport()
    {
        $sales = Sale::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->with(['customer', 'payments'])
            ->get();

        $purchases = Purchase::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->with(['supplier', 'payments'])
            ->get();

        return [
            'sales' => $sales,
            'purchases' => $purchases,
        ];
    }

    public function generateVatReport()
    {
        $sales = Sale::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->sum('tax');

        $purchases = Purchase::where('business_id', $this->business_id)
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->sum('tax');

        return [
            'output_tax' => $sales,
            'input_tax' => $purchases,
            'vat_payable' => $sales - $purchases,
        ];
    }
} 