<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 1rem; }
        .stats-grid { display:flex; gap:1rem; margin-bottom:1rem; }
        .stat-card { padding: 0.5rem; border: 1px solid #ddd; flex:1; }
        table { width:100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; }
        th { background: #f6f6f6; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ ucfirst($reportType ?? 'report') }} Report</h2>
        <div>{{ ucfirst($period ?? '') }} @if(isset($from) && isset($to)) ({{ $from->toDateString() }} to {{ $to->toDateString() }}) @endif</div>
    </div>

    @if($reportType == 'sales')
        <div style="text-align:center; margin-bottom:8px; font-size:0.95rem;">
            <strong>Summary:</strong> Total Transactions: {{ $reportData['total_transactions'] ?? 0 }} —
            Total Sales: ₱{{ number_format($reportData['total_sales'] ?? 0, 2) }} —
            Average Sale: ₱{{ number_format($reportData['average_sale'] ?? 0, 2) }}
        </div>
        @if(isset($reportData['sales']) && count($reportData['sales']) > 0)
            <table>
                <thead><tr><th>ID</th><th>Date</th><th>Customer</th><th>Items</th><th>Amount</th><th>Payment</th></tr></thead>
                <tbody>
                @foreach($reportData['sales'] as $s)
                    <tr>
                        <td>{{ $s['id'] }}</td>
                        <td>{{ $s['created_at'] }}</td>
                        <td>{{ $s['customer'] }}</td>
                        <td>{{ $s['items'] }}</td>
                        <td>₱{{ number_format($s['total_amount'], 2) }}</td>
                        <td>{{ ucfirst($s['payment_method']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p>No sales data</p>
        @endif
    @elseif($reportType == 'inventory')
        @if(isset($reportData['products']) && count($reportData['products'])>0)
            <table>
                <thead><tr><th>SKU</th><th>Name</th><th>Category</th><th>Stock</th><th>Cost</th><th>Selling</th><th>Stock Value</th></tr></thead>
                <tbody>
                @foreach($reportData['products'] as $p)
                    <tr>
                        <td>{{ $p['sku'] }}</td>
                        <td>{{ $p['name'] }}</td>
                        <td>{{ $p['category'] }}</td>
                        <td>{{ $p['stock'] }}</td>
                        <td>₱{{ number_format($p['cost_price'], 2) }}</td>
                        <td>₱{{ number_format($p['selling_price'], 2) }}</td>
                        <td>₱{{ number_format($p['stock'] * $p['cost_price'], 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="6" style="text-align:right; font-weight:bold;">Total Stock Value</td>
                    <td>₱{{ number_format($reportData['total_stock_value'] ?? 0, 2) }}</td>
                </tr>
                </tbody>
            </table>
        @else
            <p>No inventory data</p>
        @endif
    @elseif($reportType == 'customers')
        @if(isset($reportData['customers']) && count($reportData['customers'])>0)
            <table>
                <thead><tr><th>Name</th><th>Phone</th><th>Purchases</th><th>Total Spent</th><th>Last Purchase</th></tr></thead>
                <tbody>
                @foreach($reportData['customers'] as $c)
                    <tr>
                        <td>{{ $c['first_name'] }} {{ $c['last_name'] }}</td>
                        <td>{{ $c['phone'] }}</td>
                        <td>{{ $c['sales_count'] }}</td>
                        <td>₱{{ number_format($c['total_spent'], 2) }}</td>
                        <td>{{ $c['last_purchase'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p>No customer data</p>
        @endif
    @elseif($reportType == 'services')
        @if(isset($reportData['services']) && count($reportData['services']) > 0)
            <table>
                <thead><tr><th>Code</th><th>Name</th><th>Quantity</th><th>Total</th></tr></thead>
                <tbody>
                @foreach($reportData['services'] as $s)
                    <tr>
                        <td>{{ $s['code'] }}</td>
                        <td>{{ $s['name'] }}</td>
                        <td>{{ $s['quantity'] }}</td>
                        <td>₱{{ number_format($s['total'], 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p>No service data</p>
        @endif
    @endif

</body>
</html>
