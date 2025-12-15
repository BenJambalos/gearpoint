@extends('layouts.app')

@section('title', ucfirst($reportType ?? 'Report') . ' - Print')

@section('content')
@php
    $printedBy = optional(Auth::user())->name ?? config('app.name');
    $from = request('date_from');
    $to = request('date_to');
    $periodLabel = request('period') == 'custom' && $from && $to ? ($from . ' to ' . $to) : (request('period') ? ucfirst(str_replace('_', ' ', request('period'))) : 'All time');
@endphp
<style>
    @page { margin: 20mm 20mm 25mm; }
    @media print {
        /* ensure body and html don't clip or show scrollbars */
        html, body { height: auto !important; overflow: visible !important; }
        body { -webkit-print-color-adjust: exact; }
        /* hide UI chrome */
        .top-nav, .sidebar, .btn { display: none !important; }
        .main-content, .content { padding: 0 !important; }
        /* allow containers to expand for print instead of scrolling */
        .card, .card-body, .table-responsive, .table { overflow: visible !important; height: auto !important; max-height: none !important; }
        .card { box-shadow: none; border: none; }
        /* remove scrollbar artifacts for webkit browsers */
        ::-webkit-scrollbar { display: none !important; }
    }

    /* Footer for print (no header) */
    .print-footer { position: fixed; bottom: 0; left: 0; right: 0; height: 40px; text-align: center; font-size: 12px; color: #666; }
    .print-footer .pagenum:after { content: counter(page); }

    /* Table printing helpers to keep headers/footers and avoid row clipping */
    table { width: 100%; border-collapse: collapse; }
    thead { display: table-header-group; }
    tfoot { display: table-footer-group; }
    tr { page-break-inside: avoid; }
    th, td { padding: 6px; border: 1px solid #ddd; }
    th { background: #f6f6f6; }
    .summary { margin-bottom: 0.5rem; font-size: 0.95rem; }
    .card-body { margin-top: 0; }
</style>

<!-- header removed for PDF — footer only -->

<div class="card">
    <div class="card-header">{{ ucfirst(str_replace('_', ' ', $reportType)) }} Report</div>
    <div class="card-body" style="margin-top:8px;">
        <div class="summary">
            <strong>Summary:</strong>
            @if(isset($reportData['total_transactions'])) Total Transactions: {{ $reportData['total_transactions'] ?? 0 }} — @endif
            @if(isset($reportData['total_sales'])) Total Sales: ₱{{ number_format($reportData['total_sales'] ?? 0, 2) }} — @endif
            @if(isset($reportData['average_sale'])) Average Sale: ₱{{ number_format($reportData['average_sale'] ?? 0, 2) }} @endif
        </div>
        @if($reportType == 'sales')
            @if(isset($reportData['sales']) && count($reportData['sales']) > 0)
            <table>
                <thead><tr><th>ID</th><th>Date</th><th>Customer</th><th>Items</th><th>Amount</th><th>Payment</th></tr></thead>
                <tbody>
                @foreach($reportData['sales'] as $s)
                    <tr>
                        <td>#{{ str_pad($s['id'], 6, '0', STR_PAD_LEFT) }}</td>
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
    </div>
</div>

<div class="print-footer">{{ now()->format('M. d, Y') }} &nbsp;|&nbsp; Report by: {{ $printedBy }} &nbsp;|&nbsp; Page <span class="pagenum"></span></div>

<script>
    // Auto-print and close window after printing
    window.onload = function() {
        window.print();
        setTimeout(function() { window.close(); }, 100);
    }
</script>

@endsection
