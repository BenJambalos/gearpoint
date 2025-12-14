@extends('layouts.app')

@section('title', 'Reports - Motorshop POS')

@section('content')
<h2 style="margin-bottom: 1.5rem;">Reports & Analytics</h2>

<div class="card">
    <div class="card-header">Generate Report</div>
    
    <form id="reportForm" action="#" method="GET">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Report Type *</label>
                <select name="report_type" class="form-control" required>
                    <option value="sales" {{ request('report_type') == 'sales' ? 'selected' : '' }}>Sales Report</option>
                    <option value="inventory" {{ request('report_type') == 'inventory' ? 'selected' : '' }}>Inventory Report</option>
                    <option value="customers" {{ request('report_type') == 'customers' ? 'selected' : '' }}>Customer Report</option>
                    <option value="services" {{ request('report_type') == 'services' ? 'selected' : '' }}>Service Report</option>
                </select>
                <span class="form-hint">Select the type of report to generate</span>
            </div>

            <div class="form-group">
                <label class="form-label">Period *</label>
                <select name="period" class="form-control" id="periodSelect" required>
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
        </div>

        <div class="form-row" id="customDateRange" style="{{ request('period') == 'custom' ? '' : 'display: none;' }}">
            <div class="form-group">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                <span class="form-hint">Start date for report</span>
            </div>

            <div class="form-group">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                <span class="form-hint">End date for report</span>
            </div>
        </div>

        <button type="button" class="btn btn-success" id="openPrintBtn">Print Report</button>
    </form>
</div>

<div id="reportCard" class="card" style="margin-top: 1.5rem; {{ request('report_type') ? '' : 'display:none;' }}">
    <div class="card-header">
        {{ ucfirst(str_replace('_', ' ', request('report_type'))) }} Report
        @if(request('period') == 'custom')
            ({{ request('date_from') }} to {{ request('date_to') }})
        @else
            ({{ ucfirst(str_replace('_', ' ', request('period'))) }})
        @endif
    </div>

    @if(request('report_type') == 'sales')
        <!-- Sales Report -->
        <div style="margin-bottom: 0.5rem; font-size: 0.95rem; color: #2c3e50;">
            <strong>Summary:</strong>
            Total Transactions: {{ $reportData['total_transactions'] ?? 0 }} —
            Total Sales: ₱{{ number_format($reportData['total_sales'] ?? 0, 2) }} —
            Average Sale: ₱{{ number_format($reportData['average_sale'] ?? 0, 2) }}
        </div>

        @if(isset($reportData['sales']) && count($reportData['sales']) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th class="sortable" data-type="number">Transaction ID</th>
                    <th class="sortable" data-type="date">Date</th>
                    <th class="sortable" data-type="string">Customer</th>
                    <th class="sortable" data-type="number">Items</th>
                    <th class="sortable" data-type="number">Amount</th>
                    <th class="sortable" data-type="string">Payment Method</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['sales'] as $sale)
                @php
                    $saleId = is_array($sale) ? $sale['id'] : $sale->id;
                    $saleCreated = is_array($sale) ? $sale['created_at'] : $sale->created_at->format('M d, Y h:i A');
                    $saleCustomer = is_array($sale) ? ($sale['customer'] ?? 'Walk-in') : ($sale->customer ? $sale->customer->first_name . ' ' . $sale->customer->last_name : 'Walk-in');
                    $saleItems = is_array($sale) ? ($sale['items'] ?? 0) : $sale->saleItems->sum('quantity');
                    $saleTotal = is_array($sale) ? $sale['total_amount'] : $sale->total_amount;
                    $salePayment = is_array($sale) ? ucfirst($sale['payment_method'] ?? '') : ucfirst($sale->payment_method);
                @endphp
                <tr>
                    <td>#{{ str_pad($saleId, 6, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $saleCreated }}</td>
                    <td>{{ $saleCustomer }}</td>
                    <td>{{ $saleItems }}</td>
                    <td>₱{{ number_format($saleTotal, 2) }}</td>
                    <td>{{ $salePayment }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #7f8c8d; padding: 2rem;">No sales data available for this period</p>
        @endif

    @elseif(request('report_type') == 'inventory')
        <!-- Inventory Report -->
        @if(isset($reportData['products']) && count($reportData['products']) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th class="sortable" data-type="string">SKU</th>
                    <th class="sortable" data-type="string">Product Name</th>
                    <th class="sortable" data-type="string">Category</th>
                    <th class="sortable" data-type="number">Current Stock</th>
                    <th class="sortable" data-type="number">Cost Price</th>
                    <th class="sortable" data-type="number">Selling Price</th>
                    <th class="sortable" data-type="number">Stock Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['products'] as $product)
                @php
                    $isArr = is_array($product);
                    $stock = $isArr ? ($product['stock'] ?? 0) : $product->stock;
                    $reorder = $isArr ? ($product['reorder_level'] ?? 0) : $product->reorder_level;
                    $rowStyle = $stock <= $reorder ? 'background: #fff3cd;' : '';
                    $sku = $isArr ? ($product['sku'] ?? '') : $product->sku;
                    $name = $isArr ? ($product['name'] ?? '') : $product->name;
                    $categoryName = $isArr ? ($product['category'] ?? '') : ($product->category ? $product->category->name : '');
                    $cost = $isArr ? ($product['cost_price'] ?? 0) : $product->cost_price;
                    $selling = $isArr ? ($product['selling_price'] ?? 0) : $product->selling_price;
                @endphp
                <tr style="{{ $rowStyle }}">
                    <td>{{ $sku }}</td>
                    <td>{{ $name }}</td>
                    <td>{{ $categoryName }}</td>
                    <td>{{ $stock }}</td>
                    <td>₱{{ number_format($cost, 2) }}</td>
                    <td>₱{{ number_format($selling, 2) }}</td>
                    <td>₱{{ number_format($stock * $cost, 2) }}</td>
                </tr>
                @endforeach
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td colspan="6" style="text-align: right;">Total Stock Value:</td>
                    <td>₱{{ number_format($reportData['total_stock_value'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #7f8c8d; padding: 2rem;">No inventory data available</p>
        @endif

    @elseif(request('report_type') == 'customers')
        <!-- Customer Report -->
        @if(isset($reportData['customers']) && count($reportData['customers']) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th class="sortable" data-type="string">Customer Name</th>
                    <th class="sortable" data-type="string">Phone</th>
                    <th class="sortable" data-type="number">Total Purchases</th>
                    <th class="sortable" data-type="number">Total Spent</th>
                    <th class="sortable" data-type="date">Last Purchase</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['customers'] as $customer)
                @php
                    $isArr = is_array($customer);
                    $fname = $isArr ? ($customer['first_name'] ?? '') : $customer->first_name;
                    $lname = $isArr ? ($customer['last_name'] ?? '') : $customer->last_name;
                    $phone = $isArr ? ($customer['phone'] ?? '') : $customer->phone;
                    $salesCount = $isArr ? ($customer['sales_count'] ?? 0) : $customer->sales_count;
                    $totalSpent = $isArr ? ($customer['total_spent'] ?? 0) : $customer->total_spent;
                    $lastPurchase = $isArr ? ($customer['last_purchase'] ?? null) : ($customer->last_purchase ? $customer->last_purchase->format('M d, Y') : null);
                @endphp
                <tr>
                    <td>{{ $fname }} {{ $lname }}</td>
                    <td>{{ $phone }}</td>
                    <td>{{ $salesCount }}</td>
                    <td>₱{{ number_format($totalSpent, 2) }}</td>
                    <td>{{ $lastPurchase ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #7f8c8d; padding: 2rem;">No customer data available</p>
        @endif

    @endif
</div>
<div id="reportEmpty" class="card" style="margin-top: 1.5rem; {{ request('report_type') ? 'display:none;' : '' }}">
    <div class="card-header">Report Preview</div>
    <div style="padding: 2rem; text-align: center; color: #7f8c8d;">
        <p>Select report parameters to view results</p>
    </div>
</div>

<script>
document.getElementById('periodSelect').addEventListener('change', function() {
    const customRange = document.getElementById('customDateRange');
    if (this.value === 'custom') {
        customRange.style.display = 'grid';
    } else {
        customRange.style.display = 'none';
    }
});

// AJAX-driven report fetching and PDF link
function getFormParams() {
    const form = document.getElementById('reportForm');
    const data = new URLSearchParams();
    data.set('report_type', form.querySelector('[name=report_type]').value);
    data.set('period', form.querySelector('[name=period]').value);
    const dateFrom = form.querySelector('[name=date_from]').value;
    const dateTo = form.querySelector('[name=date_to]').value;
    if (dateFrom) data.set('date_from', dateFrom);
    if (dateTo) data.set('date_to', dateTo);
    return data.toString();
}

function buildSalesHtml(data, periodLabel) {
    let html = '';
    html += `<div class="card-header">Sales Report (${periodLabel})</div>`;
    html += `<div style="margin-bottom:0.5rem; font-size:0.95rem; color:#2c3e50;">`;
    html += `<strong>Summary:</strong> Total Transactions: ${data.total_transactions || 0} — Total Sales: ₱${Number(data.total_sales || 0).toFixed(2)} — Average Sale: ₱${Number(data.average_sale || 0).toFixed(2)}`;
    html += `</div>`;

    if (data.sales && data.sales.length > 0) {
        html += `<table class=\"table\"><thead><tr><th class=\"sortable\" data-type=\"number\">Transaction ID</th><th class=\"sortable\" data-type=\"date\">Date</th><th class=\"sortable\" data-type=\"string\">Customer</th><th class=\"sortable\" data-type=\"number\">Items</th><th class=\"sortable\" data-type=\"number\">Amount</th><th class=\"sortable\" data-type=\"string\">Payment Method</th></tr></thead><tbody>`;
        data.sales.forEach(s => {
            html += `<tr><td>#${String(s.id).padStart(6,'0')}</td><td>${s.created_at}</td><td>${s.customer}</td><td>${s.items}</td><td>₱${Number(s.total_amount).toFixed(2)}</td><td>${s.payment_method}</td></tr>`;
        });
        html += `</tbody></table>`;
    } else {
        html += `<p style="text-align:center;color:#7f8c8d;padding:2rem;">No sales data available for this period</p>`;
    }
    return html;
}

function buildInventoryHtml(data, periodLabel) {
    let html = '';
    html += `<div class="card-header">Inventory Report (${periodLabel})</div>`;
    if (data.products && data.products.length > 0) {
        html += `<table class=\"table\"><thead><tr><th class=\"sortable\" data-type=\"string\">SKU</th><th class=\"sortable\" data-type=\"string\">Product Name</th><th class=\"sortable\" data-type=\"string\">Category</th><th class=\"sortable\" data-type=\"number\">Current Stock</th><th class=\"sortable\" data-type=\"number\">Cost Price</th><th class=\"sortable\" data-type=\"number\">Selling Price</th><th class=\"sortable\" data-type=\"number\">Stock Value</th></tr></thead><tbody>`;
        data.products.forEach(p => {
            const rowStyle = (p.stock <= p.reorder_level) ? 'background: #fff3cd;' : '';
            html += `<tr style=\"${rowStyle}\"><td>${p.sku}</td><td>${p.name}</td><td>${p.category||''}</td><td>${p.stock}</td><td>₱${Number(p.cost_price).toFixed(2)}</td><td>₱${Number(p.selling_price).toFixed(2)}</td><td>₱${(p.stock * p.cost_price).toFixed(2)}</td></tr>`;
        });
        html += `<tr style=\"background:#f8f9fa;font-weight:bold;\"><td colspan=\"6\" style=\"text-align:right;\">Total Stock Value:</td><td>₱${Number(data.total_stock_value||0).toFixed(2)}</td></tr>`;
        html += `</tbody></table>`;
    } else {
        html += `<p style=\"text-align:center;color:#7f8c8d;padding:2rem;\">No inventory data available</p>`;
    }
    return html;
}

function buildCustomersHtml(data, periodLabel) {
    let html = '';
    html += `<div class=\"card-header\">Customer Report (${periodLabel})</div>`;
    if (data.customers && data.customers.length > 0) {
        html += `<table class=\"table\"><thead><tr><th class=\"sortable\" data-type=\"string\">Customer Name</th><th class=\"sortable\" data-type=\"string\">Phone</th><th class=\"sortable\" data-type=\"number\">Total Purchases</th><th class=\"sortable\" data-type=\"number\">Total Spent</th><th class=\"sortable\" data-type=\"date\">Last Purchase</th></tr></thead><tbody>`;
        data.customers.forEach(c => {
            html += `<tr><td>${c.first_name} ${c.last_name}</td><td>${c.phone}</td><td>${c.sales_count}</td><td>₱${Number(c.total_spent).toFixed(2)}</td><td>${c.last_purchase||'N/A'}</td></tr>`;
        });
        html += `</tbody></table>`;
    } else {
        html += `<p style=\"text-align:center;color:#7f8c8d;padding:2rem;\">No customer data available</p>`;
    }
    return html;
}

function buildServicesHtml(data, periodLabel) {
    let html = '';
    html += `<div class=\"card-header\">Service Report (${periodLabel})</div>`;
    if (data.services && data.services.length > 0) {
        html += `<table class=\"table\"><thead><tr><th>Code</th><th>Name</th><th>Quantity</th><th>Total</th></tr></thead><tbody>`;
        data.services.forEach(s => {
            html += `<tr><td>${s.code}</td><td>${s.name}</td><td>${s.quantity}</td><td>₱${Number(s.total).toFixed(2)}</td></tr>`;
        });
        html += `</tbody></table>`;
    } else {
        html += `<p style=\"text-align:center;color:#7f8c8d;padding:2rem;\">No service data available</p>`;
    }
    return html;
}

async function fetchReport() {
    const qs = getFormParams();
    const url = '/api/reports?' + qs;
    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error('Failed to fetch report');
        const json = await res.json();
        const type = json.reportType;
        const data = json.reportData;
        const periodLabel = json.period || 'custom';
        const reportCard = document.getElementById('reportCard');
        const reportEmpty = document.getElementById('reportEmpty');
        reportCard.style.display = 'block';
        reportEmpty.style.display = 'none';
        // replace inner card content
        let content = '';
        if (type === 'sales') content = buildSalesHtml(data, periodLabel);
        else if (type === 'inventory') content = buildInventoryHtml(data, periodLabel);
        else if (type === 'customers') content = buildCustomersHtml(data, periodLabel);
        else if (type === 'services') content = buildServicesHtml(data, periodLabel);
        reportCard.innerHTML = content;
        if (typeof initTableSorters === 'function') initTableSorters();

        // Update PDF link
        const pdfBtn = document.getElementById('downloadPdf');
        if (pdfBtn) pdfBtn.onclick = function() {
            window.location = '/reports/pdf?' + qs + '&report_type=' + encodeURIComponent(type);
        };
        // Update Print link
        const printBtn = document.getElementById('openPrintBtn');
        if (printBtn) printBtn.onclick = function() {
            window.open('/reports/print?' + qs + '&report_type=' + encodeURIComponent(type), '_blank');
        };
    } catch (err) {
        console.error(err);
        alert('Unable to fetch report.');
    }
}

// wire up events
document.getElementById('reportForm').querySelectorAll('select, input').forEach(el => {
    el.addEventListener('change', function () { fetchReport(); });
});

// initial load
fetchReport();
</script>
@endsection