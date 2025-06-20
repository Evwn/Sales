<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Sales Report</title>
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
        .date {
            margin-bottom: 20px;
            font-size: 16px;
            color: #666;
        }
        .summary {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
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
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
        }
        .hourly-chart {
            margin-bottom: 30px;
        }
        .chart-bar {
            height: 30px;
            background-color: #4299e1;
            margin-bottom: 5px;
            position: relative;
        }
        .chart-label {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-weight: bold;
        }
        .chart-value {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-weight: bold;
        }
        .product-stats {
            width: 100%;
            border-collapse: collapse;
        }
        .product-stats th,
        .product-stats td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .product-stats th {
            background-color: #f8f9fa;
            font-weight: bold;
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
            <div class="report-title">Daily Sales Report</div>
            <div class="date">{{ $date }}</div>
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
                <div class="summary-item">
                    <div class="summary-value">{{ number_format($summary['total_items']) }}</div>
                    <div class="summary-label">Total Items Sold</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Hourly Sales Distribution</div>
            <div class="hourly-chart">
                @foreach($hourlyStats as $hour => $stats)
                    @php
                        $maxAmount = $hourlyStats->max('amount');
                        $percentage = ($stats['amount'] / $maxAmount) * 100;
                    @endphp
                    <div class="chart-bar" style="width: {{ $percentage }}%">
                        <span class="chart-label">{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00</span>
                        <span class="chart-value">{{ number_format($stats['amount'], 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="section">
            <div class="section-title">Top Selling Products</div>
            <table class="product-stats">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productStats->take(10) as $product)
                        <tr>
                            <td>{{ $product['name'] }}</td>
                            <td>{{ $product['quantity'] }}</td>
                            <td>{{ number_format($product['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Payment Methods</div>
            <div class="payment-methods">
                @foreach($summary['payment_methods'] as $method => $data)
                    <div class="payment-method">
                        <span>{{ ucfirst($method) }}</span>
                        <span>{{ $data['count'] }} sales ({{ number_format($data['amount'], 2) }})</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="footer">
            <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
            <p>Business ID: {{ $business->id }}</p>
        </div>
    </div>
</body>
</html> 