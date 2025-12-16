@extends('layouts.app')

@section('title', 'Voided Transactions - Motorshop POS')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-block-end:1rem;">
    <h2>Voided Transactions</h2>
    <div>
        <a href="{{ route('transactions') }}" class="btn btn-primary">Back to Transactions</a>
    </div>
</div>

<div class="card">
    <div class="card-header">Voided Transaction History ({{ $sales->total() }} items)</div>
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
                    <th class="sortable" data-type="date">Voided At</th>
                    <th class="sortable" data-type="string">Voided By</th>
                    <th class="sortable" data-type="string">Reason</th>
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
                    <td>{{ $sale->voided_at ? \Carbon\Carbon::parse($sale->voided_at)->format('Y-m-d H:i') : 'N/A' }}</td>
                    <td>{{ $sale->voidedBy? $sale->voidedBy->name : 'N/A' }}</td>
                    <td>{{ $sale->void_reason ?? '' }}</td>
                    <td style="display:flex; gap:0.5rem; align-items:center;">
                        <a href="{{ route('transactions.show', $sale->id) }}" class="btn btn-primary">View</a>
                        <form method="POST" action="{{ route('transactions.void.restore', $sale->id) }}" onsubmit="var note = prompt('Optional note for restore:'); if(note === null) return false; this.querySelector('[name=note]').value = note; return confirm('Restore transaction? This will re-apply stock and may fail if insufficient.');">
                            @csrf
                            <input type="hidden" name="note" value="">
                            <button class="btn btn-success" type="submit">Restore</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="margin-block-start:1rem; display:flex; justify-content:flex-end;">
        {{ $sales->links() }}
    </div>
</div>
@endsection
