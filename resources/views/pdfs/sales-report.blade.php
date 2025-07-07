@php
function formatCurrency($value) {
    return is_numeric($value) ? number_format($value, 2) : $value;
}
// Calculate totals from $sales
$totalQuantity = 0;
$totalBuying = 0;
$totalSelling = 0;
$totalProfit = 0;
$totalAmount = 0;
foreach ($sales as $sale) {
    foreach ($sale['items'] as $item) {
        $qty = floatval($item['quantity'] ?? 0);
        $buy = floatval($item['product']['buying_price'] ?? 0);
        $sell = floatval($item['unit_price'] ?? 0);
        $profit = ($sell - $buy) * $qty;
        $totalQuantity += $qty;
        $totalBuying += $buy * $qty;
        $totalSelling += $sell * $qty;
        $totalProfit += $profit;
    }
    $totalAmount += floatval($sale['amount'] ?? 0);
}
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #d1d5db; padding: 3px 4px; text-align: left; }
        th { background: #f3f4f6; }
        tr:nth-child(even) td { background: #f3f4f6; }
        tr:nth-child(odd) td { background: #fff; }
        tfoot td { font-weight: bold; background: #e5e7eb; }
    </style>
</head>
<body>
    <h2>Sales Report</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Business</th>
                <th>Branch</th>
                <th>Seller</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Buying Price</th>
                <th>Selling Price</th>
                <th>Profit</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
        @php $rowNum = 1; @endphp
        @foreach($sales as $sale)
            @foreach($sale['items'] as $itemIdx => $item)
            <tr>
                @if($itemIdx === 0)
                    <td rowspan="{{ count($sale['items']) }}">{{ $rowNum }}</td>
                    <td rowspan="{{ count($sale['items']) }}">{{ \Carbon\Carbon::parse($sale['created_at'])->format('d/m/Y') }}</td>
                    <td rowspan="{{ count($sale['items']) }}">{{ $sale['business']['name'] ?? '' }}</td>
                    <td rowspan="{{ count($sale['items']) }}">{{ $sale['branch']['name'] ?? '' }}</td>
                    <td rowspan="{{ count($sale['items']) }}">{{ $sale['seller']['name'] ?? '' }}</td>
                @endif
                <td>{{ $item['product_name'] ?? ($item['product']['inventoryItem']['name'] ?? $item['product']['name'] ?? 'N/A') }}</td>
                <td>{{ $item['quantity'] ?? '' }}</td>
                <td>{{ formatCurrency($item['product']['buying_price'] ?? '') }}</td>
                <td>{{ formatCurrency($item['unit_price'] ?? '') }}</td>
                <td>{{ isset($item['unit_price'], $item['product']['buying_price'], $item['quantity']) ? formatCurrency(($item['unit_price'] - $item['product']['buying_price']) * $item['quantity']) : '' }}</td>
                @if($itemIdx === 0)
                    <td rowspan="{{ count($sale['items']) }}">{{ formatCurrency($sale['amount'] ?? '') }}</td>
                @endif
            </tr>
            @endforeach
            @php $rowNum++; @endphp
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td colspan="5"></td>
                <td>{{ $totalQuantity }}</td>
                <td>{{ number_format($totalBuying, 2) }}</td>
                <td>{{ number_format($totalSelling, 2) }}</td>
                <td>{{ number_format($totalProfit, 2) }}</td>
                <td>{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html> 