@php
$totalProfit = 0;
$periodLabel = isset($granularity) ? ucfirst($granularity) : 'Period';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss Report</title>
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
    <h2>Profit & Loss Report</h2>
    <table>
        <thead>
            <tr>
                <th>{{ $periodLabel }}</th>
                <th>Profit</th>
            </tr>
        </thead>
        <tbody>
        @foreach($plData as $row)
            @php $profit = floatval($row['profit'] ?? 0); $totalProfit += $profit; @endphp
            <tr>
                <td>{{ $row['month'] ?? '' }}</td>
                <td>{{ number_format($profit, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td>{{ number_format($totalProfit, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html> 