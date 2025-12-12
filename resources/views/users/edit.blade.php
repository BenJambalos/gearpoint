@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="card">
        <div class="card-header">Edit User</div>
        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="cashier" {{ $user->role === 'cashier' ? 'selected' : '' }}>Cashier</option>
                        <option value="manager" {{ $user->role === 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="mechanic" {{ $user->role === 'mechanic' ? 'selected' : '' }}>Mechanic</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="form-group">
                    <button class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection
