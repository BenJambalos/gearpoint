@extends('layouts.app')

@section('title', 'Add Supplier - Motorshop POS')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">Add New Supplier</h2>
    <a href="{{ route('suppliers') }}" class="btn btn-danger">← Back to Suppliers</a>
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
    <div class="card-header">Supplier Information</div>
    
    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Company Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g., ABC Motor Parts Supplier" value="{{ old('name') }}" required autofocus>
                <span class="form-hint">💡 Official supplier company name</span>
            </div>

            <div class="form-group">
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-control" placeholder="e.g., Maria Santos" value="{{ old('contact_person') }}">
                <span class="form-hint">💡 Main contact person's name (optional)</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input type="tel" name="phone" class="form-control" placeholder="e.g., 02-1234-5678 or 09123456789" value="{{ old('phone') }}" required>
                <span class="form-hint">💡 Primary contact number</span>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="supplier@example.com" value="{{ old('email') }}">
                <span class="form-hint">💡 For purchase orders and invoices (optional)</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Business Address *</label>
            <textarea name="address" class="form-control" rows="3" placeholder="Complete business address - warehouse or office location" required>{{ old('address') }}</textarea>
            <span class="form-hint">💡 Warehouse or office location</span>
        </div>

        <div class="form-group">
            <label class="form-label">Payment Terms</label>
            <select name="payment_terms" class="form-control">
                <option value="Cash on Delivery" {{ old('payment_terms') == 'Cash on Delivery' ? 'selected' : '' }}>Cash on Delivery</option>
                <option value="15 Days Credit" {{ old('payment_terms') == '15 Days Credit' ? 'selected' : '' }}>15 Days Credit</option>
                <option value="30 Days Credit" {{ old('payment_terms') == '30 Days Credit' ? 'selected' : '' }}>30 Days Credit</option>
                <option value="45 Days Credit" {{ old('payment_terms') == '45 Days Credit' ? 'selected' : '' }}>45 Days Credit</option>
                <option value="60 Days Credit" {{ old('payment_terms') == '60 Days Credit' ? 'selected' : '' }}>60 Days Credit</option>
            </select>
            <span class="form-hint">💡 Payment agreement with this supplier</span>
        </div>

        <div style="display: flex; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid #ddd;">
            <button type="submit" class="btn btn-success">✓ Save Supplier</button>
            <a href="{{ route('suppliers') }}" class="btn btn-danger">✗ Cancel</a>
        </div>
    </form>
</div>
@endsection