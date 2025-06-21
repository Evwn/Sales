<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alert</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #dc3545;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .alert-icon {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 10px;
        }
        .business-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .alert-title {
            font-size: 20px;
            color: #dc3545;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #555;
        }
        .message {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .products-table th {
            background: #dc3545;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .products-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .products-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .stock-critical {
            color: #dc3545;
            font-weight: bold;
        }
        .stock-low {
            color: #fd7e14;
            font-weight: bold;
        }
        .action-section {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .action-title {
            font-size: 18px;
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 15px;
        }
        .action-list {
            list-style: none;
            padding: 0;
        }
        .action-list li {
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
        }
        .action-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #4caf50;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .contact-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="alert-icon">🚨</div>
            <div class="business-name">{{ $business->name }}</div>
            <div class="alert-title">Low Stock Alert</div>
        </div>

        <div class="greeting">
            Dear {{ $businessOwner->name }},
        </div>

        <div class="message">
            <strong>⚠️ Attention Required:</strong> Some products in your inventory have reached or fallen below their minimum stock levels. Immediate action is recommended to prevent stockouts and maintain smooth business operations.
        </div>

        <h3 style="color: #2c3e50; margin-bottom: 15px;">📦 Products Requiring Restock:</h3>
        
        <table class="products-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Current Stock</th>
                    <th>Min Stock Level</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockProducts as $product)
                <tr>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td class="{{ $product->stock <= $product->min_stock_level ? 'stock-critical' : 'stock-low' }}">
                        {{ $product->stock }}
                    </td>
                    <td>{{ $product->min_stock_level }}</td>
                    <td>
                        @if($product->stock <= $product->min_stock_level)
                            <span style="color: #dc3545; font-weight: bold;">CRITICAL</span>
                        @else
                            <span style="color: #fd7e14; font-weight: bold;">LOW</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="action-section">
            <div class="action-title">🛠️ Recommended Actions:</div>
            <ul class="action-list">
                <li>Review your supplier contracts and place restock orders</li>
                <li>Consider increasing minimum stock levels for frequently sold items</li>
                <li>Analyze sales patterns to optimize inventory management</li>
                <li>Set up automatic reorder points for critical products</li>
                <li>Monitor stock levels more frequently during peak seasons</li>
            </ul>
        </div>

        <div class="contact-info">
            <strong>Need Help?</strong><br>
            If you need assistance with inventory management or have questions about restocking, please don't hesitate to contact our support team.
        </div>

        <div class="footer">
            <p>This is an automated notification from your inventory management system.</p>
            <p><strong>{{ $business->name }}</strong> - Inventory Management System</p>
            <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>
    </div>
</body>
</html> 