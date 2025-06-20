<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .report {
            max-width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 10px;
        }
        .business-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 20px;
            margin-bottom: 20px;
        }
        .date-range {
            margin-bottom: 20px;
        }
        .summary {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #2d3748;
        }
        .summary-label {
            font-size: 14px;
            color: #718096;
        }
        .payment-methods {
            margin-bottom: 30px;
        }
        .payment-method {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .sales-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .sales-table th,
        .sales-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .sales-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="report">
        <div class="header">
            @if($business->logo)
                <img src="{{ $business->logo }}" alt="Business Logo" class="logo">
            @endif
            <div class="business-name">{{ $business->name }}</div>
            <div class="report-title">Sales Report</div>
            <div class="date-range">
                @if($startDate && $endDate)
                    {{ $startDate }} to {{ $endDate }}
                @else
                    All Time
                @endif
            </div>
        </div>

        <div class="summary">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value">{{ $summary['total_sales'] }}</div>
                    <div class="summary-label">Total Sales</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">{{ number_format($summary['total_amount'], 2) }}</div>
                    <div class="summary-label">Total Amount</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">{{ number_format($summary['average_amount'], 2) }}</div>
                    <div class="summary-label">Average Sale</div>
                </div>
            </div>
        </div>

        <div class="payment-methods">
            <h3>Payment Methods</h3>
            @foreach($summary['payment_methods'] as $method => $data)
                <div class="payment-method">
                    <span>{{ ucfirst($method) }}</span>
                    <span>{{ $data['count'] }} sales ({{ number_format($data['amount'], 2) }})</span>
                </div>
            @endforeach
        </div>

        <table class="sales-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Seller</th>
                    <th>Branch</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->reference }}</td>
                        <td>{{ $sale->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ number_format($sale->total_amount, 2) }}</td>
                        <td>{{ $sale->payment_method }}</td>
                        <td>{{ $sale->seller->name }}</td>
                        <td>{{ $sale->branch->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
            <p>Business ID: {{ $business->id }}</p>
        </div>
    </div>
</body>
</html> 