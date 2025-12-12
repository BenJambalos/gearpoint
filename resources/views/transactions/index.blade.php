@extends('layouts.app')

@section('title', 'Transactions - Motorshop POS')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h2>Transactions</h2>
</div>

<div class="card">
    <div class="card-header">Transaction History ({{ $sales->total() }} items)</div>
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Cashier</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $sale->customer? $sale->customer->first_name . ' ' . ($sale->customer->last_name ?? '') : 'Walk-in' }}</td>
                    <td>{{ $sale->user? $sale->user->name : 'N/A' }}</td>
                    <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                    <td>{{ ucfirst($sale->payment_method) }}</td>
                    <td><a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-primary">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="margin-top:1rem; display:flex; justify-content:flex-end;">
        {{ $sales->links() }}
    </div>
</div>
@endsection
