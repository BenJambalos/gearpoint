@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div style="max-width: 420px; margin: 2rem auto;">
        <div class="card">
            <div class="card-header">Login</div>
            <div class="card-body">
                @if ($errors->any())
                    <div style="margin-bottom: 1rem; color: red;">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="remember"> Remember me</label>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
