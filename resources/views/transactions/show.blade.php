@extends('layouts.app')

@section('title', 'Transaction Details - Motorshop POS')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-block-end:1rem;">
    <h2>Transaction #{{ $sale->id }}</h2>
    <a href="{{ route('transactions') }}" class="btn btn-primary">Back to Transactions</a>
</div>

@if(session('success'))
<div style="background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:0.75rem; border-radius:4px; margin-block-end:1rem;">{{ session('success') }}</div>
@endif

@if($errors->has('void'))
<div style="background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:0.75rem; border-radius:4px; margin-block-end:1rem;">{{ $errors->first('void') }}</div>
@endif

@if($sale->is_void)
<div class="card" style="background: #fff1f0; border-inline-start: 4px solid #dc3545;">
    <div style="padding: .75rem;">
        <strong>Voided:</strong>
        <div>Voided At: {{ $sale->voided_at ? $sale->voided_at->format('Y-m-d H:i') : 'N/A' }}</div>
        <div>Voided By: {{ $sale->voidedBy? $sale->voidedBy->name : 'N/A' }}</div>
        <div>Reason: {{ $sale->void_reason ?? 'N/A' }}</div>

        @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
            <div style="margin-block-start:0.75rem;">
                <form method="POST" action="{{ route('transactions.void.restore', $sale->id) }}" style="display:flex; gap:0.5rem; align-items:center;">
                    @csrf
                    <input type="text" name="note" class="form-control" placeholder="Optional note for restore" style="padding:0.35rem; max-inline-size:360px;">
                    <button class="btn btn-success" type="submit">Restore Transaction</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">Transaction Details</div>
    <div style="padding:1rem;">
        <div style="display:flex; gap: 1rem; margin-block-end: 1rem;">
            <div><strong>Date:</strong> {{ $sale->created_at->format('Y-m-d H:i') }}</div>
            <div><strong>Customer:</strong> {{ $sale->customer? $sale->customer->first_name . ' ' . ($sale->customer->last_name ?? '') : 'Walk-in' }}</div>
            <div><strong>Cashier:</strong> {{ $sale->user? $sale->user->name : 'N/A' }}</div>
        </div>

        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th class="sortable" data-type="string">Item</th>
                        <th class="sortable" data-type="string">Type</th>
                        <th class="sortable" data-type="string">Code/SKU</th>
                        <th class="sortable" data-type="number">Qty</th>
                        <th class="sortable" data-type="number">Price</th>
                        <th class="sortable" data-type="number">Subtotal</th>
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

        <div style="display:flex; justify-content: flex-end; gap: 1rem; margin-block-start: 1rem;">
            <div><strong>Total:</strong> ₱{{ number_format($sale->total_amount, 2) }}</div>
            <div><strong>Paid:</strong> ₱{{ number_format($sale->amount_paid, 2) }}</div>
            <div><strong>Change:</strong> ₱{{ number_format($sale->change_due, 2) }}</div>
        </div>

        {{-- Void actions and requests --}}
        @if(!$sale->is_void)
            <div style="margin-block-start: 1rem;">
                @php $pending = $sale->voidRequests->where('status', 'pending')->first(); @endphp

                @if(auth()->user() && auth()->user()->isCashier())
                    @if($pending)
                        <div style="background:#fff3cd;padding:0.75rem;border-radius:4px;">
                            <strong>Void Request Pending</strong><br>
                            Requested by: {{ $pending->requester? $pending->requester->name : 'Unknown' }} at {{ $pending->requested_at->format('Y-m-d H:i') }}<br>
                            Reason: {{ $pending->reason }}
                        </div>
                    @else
                        <form method="POST" action="{{ route('transactions.void.request', $sale->id) }}" style="display:flex; flex-direction:column; gap:0.5rem; max-inline-size:600px;">
                            @csrf
                            <label class="form-label">Reason for void request</label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why this transaction should be voided (e.g., duplicate, incorrect items)"></textarea>
                            <div style="display:flex; gap:0.5rem;">
                                <button type="submit" class="btn btn-danger">Request Void</button>
                            </div>
                        </form>
                    @endif
                @endif

                @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                    <div style="margin-block-start:0.75rem;">
                        <strong>Void Requests</strong>
                        @if($sale->voidRequests && $sale->voidRequests->count() > 0)
                            <ul style="margin-block-start:0.5rem;">
                                @foreach($sale->voidRequests as $req)
                                    <li style="margin-block-end:0.5rem;">
                                        <strong>{{ ucfirst($req->status) }}</strong> — Requested by: {{ $req->requester? $req->requester->name : 'Unknown' }} at {{ $req->requested_at->format('Y-m-d H:i') }}<br>
                                        Reason: {{ $req->reason }}
                                        @if($req->status === 'pending')
                                            <div style="margin-block-start:0.5rem; display:flex; gap:0.5rem; align-items:flex-start;">
                                                <form method="POST" action="{{ route('transactions.void.approve', $sale->id) }}" style="display:flex; flex-direction:column; gap:0.4rem;">
                                                    @csrf
                                                    <label class="form-label">Void Reason (required)</label>
                                                    <textarea name="void_reason" class="form-control" rows="3" required placeholder="Provide reason for voiding">{{ $req->reason }}</textarea>
                                                    <div style="display:flex; gap:0.5rem;">
                                                        <button class="btn btn-success" type="submit">Approve & Void</button>
                                                    </div>
                                                </form>

                                                <form method="POST" action="{{ route('transactions.void.reject', $sale->id) }}" style="display:flex; gap:0.5rem; align-items:center;">
                                                    @csrf
                                                    <input type="text" name="note" placeholder="Optional rejection note" class="form-control" style="padding:0.35rem;">
                                                    <button class="btn btn-danger" type="submit">Reject</button>
                                                </form>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div style="margin-block-start:0.5rem; color:#7f8c8d;">No requests yet. You can void this transaction directly.</div>
                            <div style="margin-block-start:0.5rem; display:flex; gap:0.5rem; align-items:flex-start;">
                                <form method="POST" action="{{ route('transactions.void.approve', $sale->id) }}" style="display:flex; flex-direction:column; gap:0.35rem; max-inline-size:520px;">
                                    @csrf
                                    <label class="form-label">Void Reason (required)</label>
                                    <textarea name="void_reason" class="form-control" rows="3" required placeholder="Provide reason for voiding"></textarea>
                                    <div style="display:flex; gap:0.5rem;">
                                        <button class="btn btn-danger" type="submit">Void Transaction Now</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
        @if(isset($sale) && $sale->voidLogs && count($sale->voidLogs) > 0)
        <hr />
        <div><strong>Void History:</strong></div>
        <div style="margin-block-start: .5rem;">
            <ul>
                @foreach($sale->voidLogs as $log)
                    <li>{{ $log->performed_at->format('Y-m-d H:i') }} — <strong>{{ ucfirst($log->action) }}</strong> by {{ $log->user? $log->user->name : 'System' }}{{ $log->note ? ' — ' . $log->note : '' }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
