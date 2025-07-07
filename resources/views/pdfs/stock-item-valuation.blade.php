@php
$totalStock = 0;
$totalBuying = 0;
$totalSelling = 0;
$totalValue = 0;
$totalExpectedProfit = 0;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock & Item Valuation</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        tr:nth-child(even) td { background: #f3f4f6; }
        tr:nth-child(odd) td { background: #fff; }
        tfoot td { font-weight: bold; background: #e5e7eb; }
    </style>
</head>
<body>
    <h2>Stock & Item Valuation</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Stock</th>
                <th>Buying Price</th>
                <th>Selling Price</th>
                <th>Value</th>
                <th>Expected Profit</th>
            </tr>
        </thead>
        <tbody>
        @foreach($itemsValuationData as $item)
            @php
                $stock = floatval($item->stock ?? 0);
                $buy = floatval($item->buying_price ?? 0);
                $sell = floatval($item->price ?? 0);
                $value = $stock * $buy;
                $expectedProfit = ($sell - $buy) * $stock;
                $totalStock += $stock;
                $totalBuying += $stock * $buy;
                $totalSelling += $stock * $sell;
                $totalValue += $value;
                $totalExpectedProfit += $expectedProfit;
            @endphp
            <tr>
                <td>{{ $item->name ?? '' }}</td>
                <td>{{ $stock }}</td>
                <td>{{ number_format($buy, 2) }}</td>
                <td>{{ number_format($sell, 2) }}</td>
                <td>{{ number_format($value, 2) }}</td>
                <td>{{ number_format($expectedProfit, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td>{{ $totalStock }}</td>
                <td>{{ number_format($totalBuying, 2) }}</td>
                <td>{{ number_format($totalSelling, 2) }}</td>
                <td>{{ number_format($totalValue, 2) }}</td>
                <td>{{ number_format($totalExpectedProfit, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html> 