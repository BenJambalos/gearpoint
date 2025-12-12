@extends('layouts.app')

@section('title', 'Services - Motorshop POS')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">Service Management</h2>
    <a href="{{ route('services.create') }}" class="btn btn-success">+ Add New Service</a>
</div>

@if(session('success'))
<div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
    {{ session('success') }}
</div>
@endif

<div class="card">
    <div class="card-header">Service List ({{ $services->total() }} services)</div>
    
    <!-- Filters -->
    <form action="{{ route('services') }}" method="GET" style="margin-bottom: 1rem;">
        <div class="form-row">
            <div class="form-group" style="margin-bottom: 0;">
                <input type="text" name="search" class="form-control" placeholder="Search by service name or code..." value="{{ request('search') }}">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <option value="Maintenance" {{ request('category') == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="Repair" {{ request('category') == 'Repair' ? 'selected' : '' }}>Repair</option>
                    <option value="Customization" {{ request('category') == 'Customization' ? 'selected' : '' }}>Customization</option>
                    <option value="Installation" {{ request('category') == 'Installation' ? 'selected' : '' }}>Installation</option>
                    <option value="Inspection" {{ request('category') == 'Inspection' ? 'selected' : '' }}>Inspection</option>
                </select>
            </div>
        </div>
        
        <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            @if(request('search') || request('category'))
            <a href="{{ route('services') }}" class="btn btn-danger">Clear Filters</a>
            @endif
        </div>
    </form>

    @if(count($services) > 0)
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Service Name</th>
                    <th>Category</th>
                    <th style="text-align: right;">Labor Fee</th>
                    <th>Est. Duration</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $service)
                <tr>
                    <td><code>{{ $service->code ?? 'N/A' }}</code></td>
                    <td><strong>{{ $service->name }}</strong></td>
                    <td>
                        @if($service->category)
                        <span style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem;">
                            {{ $service->category }}
                        </span>
                        @else
                        N/A
                        @endif
                    </td>
                    <td style="text-align: right;"><strong>₱{{ number_format($service->labor_fee, 2) }}</strong></td>
                    <td>{{ $service->estimated_duration ?? 'N/A' }}</td>
                    <td style="text-align: center;">
                        <a href="{{ route('services.edit', $service->id) }}" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Edit</a>
                        <form action="{{ route('services.destroy', $service->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;" onclick="return confirm('Delete {{ $service->name }}?')">Delete</button>
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
            Showing {{ $services->firstItem() }} to {{ $services->lastItem() }} of {{ $services->total() }} services
        </div>
        <div>
            {{ $services->appends(request()->query())->links() }}
        </div>
    </div>
    @else
    <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
        @if(request('search') || request('category'))
            <p style="font-size: 1.1rem; margin-bottom: 1rem;">No services found matching your filters</p>
            <a href="{{ route('services') }}" class="btn btn-primary">Clear Filters</a>
        @else
            <p style="font-size: 1.1rem; margin-bottom: 1rem;">No services available yet</p>
            <a href="{{ route('services.create') }}" class="btn btn-success">Add Your First Service</a>
        @endif
    </div>
    @endif
</div>
@endsection