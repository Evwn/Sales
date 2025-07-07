@php
$totalQty = 0;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Per Item Report</title>
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
    <h2>Per Item Report</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity Sold</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
        @foreach($perItemData as $item)
            @php $totalQty += floatval($item['qty'] ?? 0); @endphp
            <tr>
                <td>{{ $item['product_name'] ?? '' }}</td>
                <td>{{ $item['qty'] ?? '' }}</td>
                <td>{{ $item['unit'] ?? 'N/A' }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td>{{ $totalQty }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html> 