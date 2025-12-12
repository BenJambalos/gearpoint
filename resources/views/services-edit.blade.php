@extends('layouts.app')

@section('title', 'Edit Service - Motorshop POS')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">Edit Service</h2>
    <a href="{{ route('services') }}" class="btn btn-danger">← Back to Services</a>
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
    <div class="card-header">Service Information</div>
    
    <form action="{{ route('services.update', $service->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Service Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $service->name) }}" required autofocus>
                <span class="form-hint">💡 Name of the service offered</span>
            </div>

            <div class="form-group">
                <label class="form-label">Service Code</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $service->code) }}">
                <span class="form-hint">💡 Unique code for this service (optional)</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Service Category</label>
                <select name="category" class="form-control">
                    <option value="">-- Select Category --</option>
                    <option value="Maintenance" {{ old('category', $service->category) == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="Repair" {{ old('category', $service->category) == 'Repair' ? 'selected' : '' }}>Repair</option>
                    <option value="Customization" {{ old('category', $service->category) == 'Customization' ? 'selected' : '' }}>Customization</option>
                    <option value="Installation" {{ old('category', $service->category) == 'Installation' ? 'selected' : '' }}>Installation</option>
                    <option value="Inspection" {{ old('category', $service->category) == 'Inspection' ? 'selected' : '' }}>Inspection</option>
                </select>
                <span class="form-hint">💡 Type of service (optional)</span>
            </div>

            <div class="form-group">
                <label class="form-label">Labor Fee (₱) *</label>
                <input type="number" name="labor_fee" class="form-control" step="0.01" value="{{ old('labor_fee', $service->labor_fee) }}" required>
                <span class="form-hint">💡 Standard price for this service</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Estimated Duration</label>
            <input type="text" name="estimated_duration" class="form-control" value="{{ old('estimated_duration', $service->estimated_duration) }}">
            <span class="form-hint">💡 How long this service typically takes (optional)</span>
        </div>

        <div class="form-group">
            <label class="form-label">Service Description</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $service->description) }}</textarea>
            <span class="form-hint">💡 What does this service include? (optional)</span>
        </div>

        <div style="display: flex; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid #ddd;">
            <button type="submit" class="btn btn-success">✓ Update Service</button>
            <a href="{{ route('services') }}" class="btn btn-danger">✗ Cancel</a>
        </div>
    </form>
</div>
@endsection