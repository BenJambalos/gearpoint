@extends('layouts.app')

@section('title', 'Dashboard - Motorshop POS')

@section('content')
<h2 style="margin-bottom: 1.5rem;">Dashboard Overview</h2>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">₱{{ number_format($todaySales ?? 0, 2) }}</div>
        <div class="stat-label">Today's Sales</div>
    </div>
    <div class="stat-card green">
        <div class="stat-value">{{ $totalProducts ?? 0 }}</div>
        <div class="stat-label">Products in Stock</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-value">{{ $totalCustomers ?? 0 }}</div>
        <div class="stat-label">Total Customers</div>
    </div>
</div>

<div class="card">
    <div class="card-header">Low Stock Alert</div>
    @if(isset($lowStockProducts) && count($lowStockProducts) > 0)
    <table class="table">
        <thead>
            <tr>
                <th class="sortable" data-type="string">SKU</th>
                <th class="sortable" data-type="string">Product Name</th>
                <th class="sortable" data-type="number">Current Stock</th>
                <th class="sortable" data-type="number">Reorder Level</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lowStockProducts as $product)
            <tr style="background: #fff3cd;">
                <td>{{ $product->sku }}</td>
                <td>{{ $product->name }}</td>
                <td><strong style="color: #dc3545;">{{ $product->stock }}</strong></td>
                <td>{{ $product->reorder_level }}</td>
                <td>
                    <a href="{{ route('inventory') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Restock</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align: center; color: #7f8c8d; padding: 1rem;">All products are well stocked!</p>
    @endif
</div>

<div class="card">
    <div class="card-header">Recent Transactions</div>
    @if(isset($recentSales) && count($recentSales) > 0)
    <table class="table">
        <thead>
            <tr>
                <th class="sortable" data-type="number">Transaction ID</th>
                <th class="sortable" data-type="string">Customer</th>
                <th class="sortable" data-type="string">Cashier</th>
                <th class="sortable" data-type="number">Amount</th>
                <th class="sortable" data-type="string">Payment Method</th>
                <th class="sortable" data-type="date">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentSales as $sale)
            <tr>
                <td>#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $sale->customer ? $sale->customer->first_name . ' ' . $sale->customer->last_name : 'Walk-in' }}</td>
                <td>{{ $sale->user->name }}</td>
                <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                <td>{{ ucfirst($sale->payment_method) }}</td>
                <td>{{ $sale->created_at->format('M d, Y h:i A') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align: center; color: #7f8c8d; padding: 1rem;">No transactions yet</p>
    @endif
</div>
@endsection