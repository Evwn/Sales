<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .receipt {
            max-width: 80mm;
            margin: 0 auto;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .business-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .branch-name {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .receipt-info {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .items {
            margin-bottom: 20px;
        }
        .item-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        .item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            margin-bottom: 5px;
        }
        .totals {
            margin-bottom: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .barcode {
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            margin-top: 20px;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            @if($business->logo)
                <img src="{{ $business->logo }}" alt="Business Logo" class="logo">
            @endif
            <div class="business-name">{{ $business->name }}</div>
            <div class="branch-name">{{ $branch->name }}</div>
            <div>Sales Receipt</div>
        </div>

        <div class="receipt-info">
            <div class="info-row">
                <span>Receipt No:</span>
                <span>{{ $sale->reference }}</span>
            </div>
            <div class="info-row">
                <span>Date:</span>
                <span>{{ $sale->created_at->format('Y-m-d H:i:s') }}</span>
            </div>
            <div class="info-row">
                <span>Cashier:</span>
                <span>{{ $sale->seller->name }}</span>
            </div>
        </div>

        <div class="items">
            <div class="item-header">
                <span>Item</span>
                <span>Qty</span>
                <span>Price</span>
                <span>Total</span>
            </div>
            @foreach($sale->items as $item)
                <div class="item-row">
                    <span>{{ $item->product->name }}</span>
                    <span>{{ $item->quantity }}</span>
                    <span>{{ number_format($item->unit_price, 2) }}</span>
                    <span>{{ number_format($item->total, 2) }}</span>
                </div>
            @endforeach
        </div>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>{{ number_format($sale->amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Payment Method:</span>
                <span>{{ $sale->payment_method }}</span>
            </div>
        </div>

        <div class="barcode">
            <div style="font-family: monospace; font-size: 12px; text-align: center; margin: 10px 0;">
                <div style="border: 1px solid #000; padding: 10px; display: inline-block;">
                    {{ $sale->barcode }}
                </div>
            </div>
            <div style="text-align: center; font-size: 10px; color: #666;">Barcode: {{ $sale->barcode }}</div>
        </div>

        <div class="footer">
            <p>Thank you for your purchase!</p>
            <p>This receipt serves as proof of purchase</p>
            <p>Business ID: {{ $business->id }}</p>
            <p>Branch ID: {{ $branch->id }}</p>
        </div>
    </div>
</body>
</html> 