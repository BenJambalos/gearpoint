@extends('layouts.app')

@section('title', 'Add Product - Motorshop POS')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">Add New Product</h2>
    <a href="{{ route('inventory') }}" class="btn btn-danger">← Back to Inventory</a>
</div>

@if($errors->any())
<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
    <strong>Please fix the following errors:</strong>
    <ul style="margin: 0.5rem 0 0 1.5rem;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card">
    <div class="card-header">Product Information</div>
    
    <form action="{{ route('inventory.store') }}" method="POST">
        @csrf
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g., Motor Oil 10W-40" value="{{ old('name') }}" required autofocus>
                <span class="form-hint">💡 Enter the full product name</span>
            </div>

            <div class="form-group">
                <label class="form-label">Product Code/SKU *</label>
                <input type="text" name="sku" class="form-control" placeholder="e.g., MO-10W40-001" value="{{ old('sku') }}" required>
                <span class="form-hint">💡 Unique identifier for this product</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
                <span class="form-hint">💡 Product category for organization</span>
            </div>

            <div class="form-group">
                <label class="form-label">Brand</label>
                <input type="text" name="brand" class="form-control" placeholder="e.g., Honda, Yamaha, Shell" value="{{ old('brand') }}">
                <span class="form-hint">💡 Manufacturer or brand name (optional)</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Cost Price (₱) *</label>
                <input type="number" name="cost_price" class="form-control" placeholder="0.00" step="0.01" value="{{ old('cost_price') }}" required>
                <span class="form-hint">💡 How much you paid for this product</span>
            </div>

            <div class="form-group">
                <label class="form-label">Selling Price (₱) *</label>
                <input type="number" name="selling_price" class="form-control" placeholder="0.00" step="0.01" value="{{ old('selling_price') }}" required>
                <span class="form-hint">💡 Price you charge customers</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Initial Stock Quantity *</label>
                <input type="number" name="stock" class="form-control" placeholder="0" value="{{ old('stock', 0) }}" required>
                <span class="form-hint">💡 Number of units currently in stock</span>
            </div>

            <div class="form-group">
                <label class="form-label">Reorder Level *</label>
                <input type="number" name="reorder_level" class="form-control" placeholder="10" value="{{ old('reorder_level', 10) }}" required>
                <span class="form-hint">💡 Alert when stock falls below this number</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Unit *</label>
                <select name="unit" class="form-control" required>
                    <option value="piece" {{ old('unit', 'piece') == 'piece' ? 'selected' : '' }}>Piece</option>
                    <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>Liter</option>
                    <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box</option>
                    <option value="set" {{ old('unit') == 'set' ? 'selected' : '' }}>Set</option>
                    <option value="pair" {{ old('unit') == 'pair' ? 'selected' : '' }}>Pair</option>
                    <option value="kilogram" {{ old('unit') == 'kilogram' ? 'selected' : '' }}>Kilogram</option>
                </select>
                <span class="form-hint">💡 Unit of measurement</span>
            </div>

            <div class="form-group">
                <!-- Empty space for alignment -->
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Product description, specifications, compatibility information...">{{ old('description') }}</textarea>
            <span class="form-hint">💡 Additional details about the product (optional)</span>
        </div>

        <div style="display: flex; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid #ddd;">
            <button type="submit" class="btn btn-success">✓ Save Product</button>
            <a href="{{ route('inventory') }}" class="btn btn-danger">✗ Cancel</a>
        </div>
    </form>
</div>
@endsection