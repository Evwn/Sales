@php
$totalStock = 0;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Report</title>
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
    <h2>Stock Report</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
        @foreach($stockData as $item)
            @php $totalStock += floatval($item['stock'] ?? 0); @endphp
            <tr>
                <td>{{ $item['name'] ?? '' }}</td>
                <td>{{ $item['stock'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td>{{ $totalStock }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html> 