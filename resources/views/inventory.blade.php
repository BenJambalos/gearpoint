@extends('layouts.app')

@section('title', 'Inventory - Motorshop POS')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">Inventory Management</h2>
    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
        <a href="{{ route('inventory.create') }}" class="btn btn-success">+ Add New Product</a>
    @endif
</div>

@if(session('success'))
<div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
    {{ session('success') }}
</div>
@endif

<div class="card">
    <div class="card-header">Inventory List ({{ $products->total() }} items)</div>
    
    <!-- Filters -->
    <form action="{{ route('inventory') }}" method="GET" style="margin-bottom: 1rem;">
        <div class="form-row">
            <div class="form-group" style="margin-bottom: 0;">
                <input type="text" name="search" class="form-control" placeholder="Search by name, SKU, or brand..." value="{{ request('search') }}">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            @if(request('search') || request('category'))
            <a href="{{ route('inventory') }}" class="btn btn-danger">Clear Filters</a>
            @endif
            
            <div style="margin-left: auto;">
                <label style="display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()">
                    <span>Show Low Stock Only</span>
                </label>
            </div>
        </div>
    </form>

    @if(count($products) > 0)
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th class="sortable" data-type="string">SKU</th>
                    <th class="sortable" data-type="string">Product Name</th>
                    <th class="sortable" data-type="string">Category</th>
                    <th class="sortable" data-type="string">Brand</th>
                    <th class="sortable" data-type="number" style="text-align: center;">Stock</th>
                    <th class="sortable" data-type="number" style="text-align: right;">Cost Price</th>
                    <th class="sortable" data-type="number" style="text-align: right;">Selling Price</th>
                    <th class="sortable" data-type="string" style="text-align: center;">Expiry</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                @php
                    $expiry = $product->expiry_date ? \Carbon\Carbon::parse($product->expiry_date) : null;
                    $now = \Carbon\Carbon::now();
                    // Treat "almost expired" as within 30 days
                    $almostExpired = $expiry ? ($expiry->greaterThanOrEqualTo($now) && $expiry->lessThanOrEqualTo($now->copy()->addDays(30))) : false;
                    // Treat "almost low" as stock above reorder level but within 50% of reorder level or +5 units
                    $almostLow = false;
                    if ($product->reorder_level && $product->stock > $product->reorder_level) {
                        $buffer = max(1, intval($product->reorder_level * 0.5));
                        $buffer = max($buffer, 5);
                        $almostLow = $product->stock <= ($product->reorder_level + $buffer);
                    }
                    $lowStock = $product->stock <= $product->reorder_level;
                    // Row style precedence: almostExpired (orange) -> lowStock (yellow) -> almostLow (soft yellow)
                    $rowStyle = $almostExpired ? 'background: #fff4e6;' : ($lowStock ? 'background: #fff3cd;' : ($almostLow ? 'background: #fffef0;' : ''));
                @endphp
                <tr style="{{ $rowStyle }}">
                    <td><code>{{ $product->sku }}</code></td>
                    <td>
                        <strong>{{ $product->name }}</strong>
                        @if($product->stock <= $product->reorder_level)
                        <br><span style="color: #dc3545; font-size: 0.85rem;">⚠️ Low Stock Alert</span>
                        @endif
                    </td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ $product->brand ?? 'N/A' }}</td>
                    <td style="text-align: center;">
                        <strong style="{{ $product->stock <= $product->reorder_level ? 'color: #dc3545;' : 'color: #28a745;' }}">
                            {{ $product->stock }}
                        </strong>
                        <span style="color: #7f8c8d; font-size: 0.9rem;">{{ $product->unit }}</span>
                    </td>
                    <td style="text-align: right;">₱{{ number_format($product->cost_price, 2) }}</td>
                    <td style="text-align: right;">₱{{ number_format($product->selling_price, 2) }}</td>
                    <td style="text-align: center;">{{ $product->expiry_date ? \Carbon\Carbon::parse($product->expiry_date)->format('M d, Y') : '—' }}</td>
                    <td style="text-align: center;">
                        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                            <a href="{{ route('inventory.edit', $product->id) }}" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Edit</a>
                            <form action="{{ route('inventory.destroy', $product->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;" onclick="return confirm('Delete {{ $product->name }}?')">Delete</button>
                            </form>
                        @else
                            <!-- Cashiers / others: view-only -->
                            <span style="color:#7f8c8d; font-size:0.9rem;">View only</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="color: #7f8c8d; font-size: 0.9rem;">
            Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
        </div>
        <div style="display:flex; gap: 1rem; align-items: center;">
            <div style="color: #7f8c8d; font-size: 0.9rem;">
                Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
            </div>
            <div>
                {{ $products->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
            @else
    <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
        @if(request('search') || request('category') || request('low_stock'))
            <p style="font-size: 1.1rem; margin-bottom: 1rem;">No products found matching your filters</p>
            <a href="{{ route('inventory') }}" class="btn btn-primary">Clear Filters</a>
        @else
            <p style="font-size: 1.1rem; margin-bottom: 1rem;">No products in inventory yet</p>
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                <a href="{{ route('inventory.create') }}" class="btn btn-success">Add Your First Product</a>
            @endif
        @endif
    </div>
    @endif
</div>
@endsection