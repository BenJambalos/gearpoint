@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
    <div style="max-width: 420px; margin: 2rem auto;">
        <div class="card">
            <div class="card-header">Forgot Password</div>
            <div class="card-body">
                @if (session('success'))
                    <div style="margin-bottom: 1rem; color: green">{{ session('success') }}</div>
                @endif
                @if (session('reset_link'))
                    <!-- fallback links removed for security; see logs for details -->
                @endif
                @if ($errors->any())
                    <div style="margin-bottom: 1rem; color: red;">{{ $errors->first() }}</div>
                @endif
                <!-- Mail debug and Gmail-specific guidance removed per request. Configure mail settings in .env. -->
                @if(config('app.debug'))
                    <div style="margin-bottom: .5rem; color: #666; font-size: .85rem;">Mail: {{ config('mail.mailers.smtp.host') ?? 'unknown' }}:{{ config('mail.mailers.smtp.port') ?? 'unknown' }}</div>
                @endif
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="email" autocapitalize="off" autocorrect="off" required autofocus>
                    </div>
                    <div class="form-group" style="display:flex; gap:.5rem; align-items:center;">
                        <button class="btn btn-primary">Send Reset Link</button>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary" title="Back to Login">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
