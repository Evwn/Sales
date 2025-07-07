@php
$totalProfit = 0;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit Report</title>
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
    <h2>Profit Report</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Business</th>
                <th>Branch</th>
                <th>Seller</th>
                <th>Products</th>
                <th>Profit</th>
            </tr>
        </thead>
        <tbody>
        @foreach($profitData as $idx => $row)
            @php $totalProfit += floatval($row['profit'] ?? 0); @endphp
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $row['date'] ?? '' }}</td>
                <td>{{ $row['business'] ?? '' }}</td>
                <td>{{ $row['branch'] ?? '' }}</td>
                <td>{{ $row['seller'] ?? '' }}</td>
                <td>{!! $row['products'] ?? '' !!}</td>
                <td>{{ number_format($row['profit'] ?? 0, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td colspan="5"></td>
                <td>{{ number_format($totalProfit, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html> 