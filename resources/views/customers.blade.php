@extends('layouts.app')

@section('title', 'Customers - Motorshop POS')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">Customer Management</h2>
    <a href="{{ route('customers.create') }}" class="btn btn-success">+ Add New Customer</a>
</div>

@if(session('success'))
<div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
    {{ session('success') }}
</div>
@endif

<div class="card">
    <div class="card-header">Customer List ({{ $customers->total() }} customers)</div>
    
    <!-- Search Filter -->
    <form action="{{ route('customers') }}" method="GET" style="margin-bottom: 1rem;">
        <div style="display: flex; gap: 0.5rem;">
            <input type="text" name="search" class="form-control" placeholder="Search by name, phone, or vehicle..." value="{{ request('search') }}" style="flex: 1;">
            <button type="submit" class="btn btn-primary">Search</button>
            @if(request('search'))
            <a href="{{ route('customers') }}" class="btn btn-danger">Clear</a>
            @endif
        </div>
    </form>

    @if(count($customers) > 0)
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact Number</th>
                    <th>Email</th>
                    <th>Vehicle</th>
                    <th>Registered</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                <tr>
                    <td><strong>#{{ $customer->id }}</strong></td>
                    <td><strong>{{ $customer->first_name }} {{ $customer->last_name }}</strong></td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->email ?? 'N/A' }}</td>
                    <td>{{ $customer->vehicle_info ?? 'N/A' }}</td>
                    <td>{{ $customer->created_at->format('M d, Y') }}</td>
                    <td style="text-align: center;">
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Edit</a>
                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;" onclick="return confirm('Delete {{ $customer->first_name }} {{ $customer->last_name }}?')">Delete</button>
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
            Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} customers
        </div>
        <div>
            {{ $customers->appends(request()->query())->links() }}
        </div>
    </div>
    @else
    <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
        @if(request('search'))
            <p style="font-size: 1.1rem; margin-bottom: 1rem;">No customers found matching "{{ request('search') }}"</p>
            <a href="{{ route('customers') }}" class="btn btn-primary">Clear Search</a>
        @else
            <p style="font-size: 1.1rem; margin-bottom: 1rem;">No customers registered yet</p>
            <a href="{{ route('customers.create') }}" class="btn btn-success">Add Your First Customer</a>
        @endif
    </div>
    @endif
</div>
@endsection