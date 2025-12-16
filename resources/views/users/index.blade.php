@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="card">
        <div class="card-header">Users</div>
        <div class="card-body">
            @if (session('success'))
                <div style="margin-bottom: 1rem; color: green">{{ session('success') }}</div>
            @endif
            @if (session('reset_link'))
                <!-- fallback links removed for security -->
            @endif
            @if ($errors->has('send_reset'))
                <div style="margin-bottom: 1rem; color: red;">{{ $errors->first('send_reset') }}</div>
            @endif
            <!-- mail_test UI removed per request -->
            <!-- Mail log hint removed per request -->
            <!-- Mail debug and Gmail-specific guidance removed per request. Configure mail settings in .env. -->
            <a href="{{ route('users.create') }}" class="btn btn-primary" style="margin-bottom: 1rem;">Create User</a>
            <table class="table">
                <thead>
                    <tr>
                        <th class="sortable" data-type="string">Name</th>
                        <th class="sortable" data-type="string">Email</th>
                        <th class="sortable" data-type="string">Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role }}</td>
                        <td>
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-success">Edit</a>
                            @if(auth()->user()->isAdmin())
                                @if(auth()->id() !== $user->id)
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" onclick="return confirm('Delete user?')">Delete</button>
                                    </form>
                                @else
                                    <button class="btn btn-secondary" disabled title="You cannot delete your own account">Delete</button>
                                @endif
                            @endif
                            @if(auth()->user() && (auth()->user()->isManager() || auth()->user()->isAdmin()))
                                <form action="{{ route('users.sendReset', $user) }}" method="POST" style="display:inline-block; margin-left: 0.5rem;">
                                    @csrf
                                    <button class="btn btn-primary">Send Reset</button>
                                </form>
                                <!-- test email removed per request -->
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $users->links() }}
        </div>
    </div>
@endsection
