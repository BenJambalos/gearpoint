@extends('layouts.app')

@section('title', 'Add Customer - Motorshop POS')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">Add New Customer</h2>
    <a href="{{ route('customers') }}" class="btn btn-danger">← Back to Customers</a>
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
    <div class="card-header">Customer Information</div>
    
    <form action="{{ route('customers.store') }}" method="POST">
        @csrf
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">First Name *</label>
                <input type="text" name="first_name" class="form-control" placeholder="e.g., Juan" value="{{ old('first_name') }}" required autofocus>
                <span class="form-hint">💡 Customer's first name</span>
            </div>

            <div class="form-group">
                <label class="form-label">Last Name *</label>
                <input type="text" name="last_name" class="form-control" placeholder="e.g., Dela Cruz" value="{{ old('last_name') }}" required>
                <span class="form-hint">💡 Customer's last name</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Contact Number *</label>
                <input type="tel" name="phone" class="form-control" placeholder="e.g., 09123456789" value="{{ old('phone') }}" required>
                <span class="form-hint">💡 Mobile or telephone number</span>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="customer@example.com" value="{{ old('email') }}">
                <span class="form-hint">💡 For receipts and notifications (optional)</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Complete Address</label>
            <textarea name="address" class="form-control" rows="3" placeholder="Street, Barangay, City, Province">{{ old('address') }}</textarea>
            <span class="form-hint">💡 Complete address for delivery (optional)</span>
        </div>

        <div class="form-group">
            <label class="form-label">Vehicle Information</label>
            <input type="text" name="vehicle_info" class="form-control" placeholder="e.g., Honda Wave 125, ABC-1234" value="{{ old('vehicle_info') }}">
            <span class="form-hint">💡 Customer's motorcycle details - model, plate number (optional)</span>
        </div>

        <div style="display: flex; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid #ddd;">
            <button type="submit" class="btn btn-success">✓ Save Customer</button>
            <a href="{{ route('customers') }}" class="btn btn-danger">✗ Cancel</a>
        </div>
    </form>
</div>
@endsection