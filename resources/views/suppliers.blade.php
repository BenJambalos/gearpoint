@extends('layouts.app')

@section('title', 'Suppliers - Motorshop POS')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">Supplier Management</h2>
    <a href="{{ route('suppliers.create') }}" class="btn btn-success">+ Add New Supplier</a>
</div>

@if(session('success'))
<div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
    {{ session('success') }}
</div>
@endif

<div class="card">
    <div class="card-header">Supplier List ({{ $suppliers->total() }} suppliers)</div>
    
    <!-- Search Filter -->
    <form action="{{ route('suppliers') }}" method="GET" style="margin-bottom: 1rem;">
        <div style="display: flex; gap: 0.5rem;">
            <input type="text" name="search" class="form-control" placeholder="Search by company name, contact person, or phone..." value="{{ request('search') }}" style="flex: 1;">
            <button type="submit" class="btn btn-primary">Search</button>
            @if(request('search'))
            <a href="{{ route('suppliers') }}" class="btn btn-danger">Clear</a>
            @endif
        </div>
    </form>

    @if(count($suppliers) > 0)
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Company Name</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Payment Terms</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $supplier)
                <tr>
                    <td><strong>#{{ $supplier->id }}</strong></td>
                    <td><strong>{{ $supplier->name }}</strong></td>
                    <td>{{ $supplier->contact_person ?? 'N/A' }}</td>
                    <td>{{ $supplier->phone }}</td>
                    <td>{{ $supplier->email ?? 'N/A' }}</td>
                    <td>{{ $supplier->payment_terms ?? 'N/A' }}</td>
                    <td style="text-align: center;">
                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Edit</a>
                        <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;" onclick="return confirm('Delete {{ $supplier->name }}?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="color: #7f8c8d; font-size: 0.9rem;">
            Showing {{ $suppliers->firstItem() }} to {{ $suppliers->lastItem() }} of {{ $suppliers->total() }} suppliers
        </div>
        <div>
            {{ $suppliers->appends(request()->query())->links() }}
        </div>
    </div>
    @else
    <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
        @if(request('search'))
            <p style="font-size: 1.1rem; margin-bottom: 1rem;">No suppliers found matching "{{ request('search') }}"</p>
            <a href="{{ route('suppliers') }}" class="btn btn-primary">Clear Search</a>
        @else
            <p style="font-size: 1.1rem; margin-bottom: 1rem;">No suppliers registered yet</p>
            <a href="{{ route('suppliers.create') }}" class="btn btn-success">Add Your First Supplier</a>
        @endif
    </div>
    @endif
</div>
@endsection