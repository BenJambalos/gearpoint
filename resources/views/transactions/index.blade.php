@extends('layouts.app')

@section('title', 'Transactions - Motorshop POS')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h2>Transactions</h2>
    @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
        <div>
            <a href="{{ route('transactions.voided') }}" class="btn btn-danger">View Voided Transactions</a>
        </div>
    @endif
</div>

<div class="card">
    <div class="card-header">Transaction History ({{ $sales->total() }} items)</div>
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th class="sortable" data-type="number">ID</th>
                    <th class="sortable" data-type="date">Date</th>
                    <th class="sortable" data-type="string">Customer</th>
                    <th class="sortable" data-type="string">Cashier</th>
                    <th class="sortable" data-type="number">Total</th>
                    <th class="sortable" data-type="string">Payment</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr @if($sale->is_void) style="background: #fff1f0;" @endif>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $sale->customer? $sale->customer->first_name . ' ' . ($sale->customer->last_name ?? '') : 'Walk-in' }}</td>
                    <td>{{ $sale->user? $sale->user->name : 'N/A' }}</td>
                    <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                    <td>{{ ucfirst($sale->payment_method) }}</td>
                    <td>
                        <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-primary">View</a>
                        @if($sale->is_void)
                            <span style="display:inline-block; margin-left:.5rem; padding:.2rem .4rem; background:#dc3545; color:white; border-radius:4px; font-size:0.8rem;">VOID</span>
                        @elseif($sale->voidRequests && $sale->voidRequests->where('status','pending')->count() > 0)
                            <span style="display:inline-block; margin-left:.5rem; padding:.2rem .4rem; background:#fff3cd; color:#856404; border-radius:4px; font-size:0.8rem;">PENDING VOID</span>
                        @endif
                    </td>
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
