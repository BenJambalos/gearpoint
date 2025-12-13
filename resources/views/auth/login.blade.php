@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div style="max-width: 420px; margin: 2rem auto;">
        <div class="card">
            <div class="card-header">Login</div>

            <div class="card-body">
                @if ($errors->has('name'))
                    <div style="margin-bottom: 1rem; color: red;">{{ $errors->first('name') }}</div>
                @elseif ($errors->has('password'))
                    <div style="margin-bottom: 1rem; color: red;">{{ $errors->first('password') }}</div>
                @elseif ($errors->any())
                    <div style="margin-bottom: 1rem; color: red;">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" pattern="^[A-Za-z0-9_]+$" class="form-control" value="{{ old('name') }}" autocomplete="username" autocapitalize="off" autocorrect="off" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" autocomplete="current-password" required>
                    </div>
                    <div class="form-group">
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
