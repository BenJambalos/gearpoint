@extends('layouts.app')

@section('title', 'Transaction Details - Motorshop POS')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h2>Transaction #{{ $sale->id }}</h2>
    <a href="{{ route('transactions') }}" class="btn btn-primary">Back to Transactions</a>
</div>

<div class="card">
    <div class="card-header">Transaction Details</div>
    <div style="padding:1rem;">
        <div style="display:flex; gap: 1rem; margin-bottom: 1rem;">
            <div><strong>Date:</strong> {{ $sale->created_at->format('Y-m-d H:i') }}</div>
            <div><strong>Customer:</strong> {{ $sale->customer? $sale->customer->first_name . ' ' . ($sale->customer->last_name ?? '') : 'Walk-in' }}</div>
            <div><strong>Cashier:</strong> {{ $sale->user? $sale->user->name : 'N/A' }}</div>
        </div>

        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Code/SKU</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->saleItems as $item)
                    <tr>
                        <td>{{ $item->product? $item->product->name : ($item->service? $item->service->name : 'Unknown') }}</td>
                        <td>{{ $item->product? 'Product' : 'Service' }}</td>
                        <td>{{ $item->product? $item->product->sku : ($item->service? $item->service->code : '') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₱{{ number_format($item->price, 2) }}</td>
                        <td>₱{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display:flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
            <div><strong>Total:</strong> ₱{{ number_format($sale->total_amount, 2) }}</div>
            <div><strong>Paid:</strong> ₱{{ number_format($sale->amount_paid, 2) }}</div>
            <div><strong>Change:</strong> ₱{{ number_format($sale->change_due, 2) }}</div>
        </div>
    </div>
</div>
@endsection
