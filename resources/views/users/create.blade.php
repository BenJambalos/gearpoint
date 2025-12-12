@extends('layouts.app')

@section('title', 'Create User')

@section('content')
    <div class="card">
        <div class="card-header">Create User</div>
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="cashier">Cashier</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                        <option value="mechanic">Mechanic</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="form-group">
                    <button class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection
