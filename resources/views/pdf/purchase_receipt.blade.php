<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $purchase->reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { margin-bottom: 24px; display: flex; justify-content: space-between; }
        .header h2 { margin: 0 0 8px 0; }
        .info-table { width: 100%; margin-bottom: 16px; }
        .info-table td { padding: 2px 4px; }
        .section-title { font-weight: bold; margin-top: 18px; margin-bottom: 6px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 6px; }
        .items-table th { background: #f3f3f3; }
        .totals { text-align: right; margin-top: 12px; }
        .message { margin-top: 18px; }
        .destination {
            font-size: 13px;
            background: #f7f7f7;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
            max-width: 320px;
        }
        .destination strong { display: block; font-size: 14px; margin-bottom: 2px; }
        .destination .label { color: #888; font-size: 11px; margin-right: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="destination">
            <strong>Goods Destination</strong>
            <div><span class="label">Store:</span> {{ $purchase->store->name ?? 'N/A' }}</div>
            @if(!empty($purchase->store->address))
                <div><span class="label">Address:</span> {{ $purchase->store->address }}</div>
            @endif
            @if(!empty($purchase->store->phone))
                <div><span class="label">Phone:</span> {{ $purchase->store->phone }}</div>
            @endif
            @if($purchase->store && $purchase->store->business)
                <div><span class="label">Business:</span> {{ $purchase->store->business->name }}</div>
            @endif
            @if($purchase->store && $purchase->store->branch)
                <div><span class="label">Branch:</span> {{ $purchase->store->branch->name }}</div>
            @endif
        </div>
        <div>
            <h2>Purchase Order</h2>
            <table class="info-table">
                <tr>
                    <td><strong>From:</strong></td>
                    <td>{{ $purchase->orderedBy->name ?? '' }} ({{ $purchase->orderedBy->email ?? '' }})</td>
                </tr>
                <tr>
                    <td><strong>To:</strong></td>
                    <td>{{ $purchase->supplier->name ?? '' }} ({{ $purchase->supplier->email ?? '' }})</td>
                </tr>
                <tr>
                    <td><strong>Order Ref:</strong></td>
                    <td>{{ $purchase->reference }}</td>
                </tr>
                <tr>
                    <td><strong>Order Date:</strong></td>
                    <td>{{ $purchase->order_date ? $purchase->order_date->format('Y-m-d') : '' }}</td>
                </tr>
                <tr>
                    <td><strong>Expected Date:</strong></td>
                    <td>{{ $purchase->expected_date ? $purchase->expected_date->format('Y-m-d') : '' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section-title">Items</div>
    <table class="items-table">
        <thead>
            <tr>
                <th></th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Purchase Cost</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->item->name ?? '' }}</td>
                    <td>{{ $item->quantity_ordered }}</td>
                    <td>{{ number_format($item->purchase_cost, 2) }}</td>
                    <td>{{ number_format($item->quantity_ordered * $item->purchase_cost, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        @if($purchase->discount > 0)
            <span>Discount: {{ number_format($purchase->discount, 2) }}</span><br>
        @endif
        @if($purchase->tax > 0)
            <span>Tax: {{ number_format($purchase->tax, 2) }}</span><br>
        @endif
        <strong>Total:</strong> {{ number_format($purchase->items->sum(function($item) { return $item->quantity_ordered * $item->purchase_cost; }), 2) }}<br>
    </div>

    @if(!empty($message))
    <div class="message">
        <strong>Message:</strong><br>
        {!! nl2br(e($message)) !!}
    </div>
    @endif
</body>
</html> 