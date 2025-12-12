@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="card">
        <div class="card-header">Users</div>
        <div class="card-body">
            <a href="{{ route('users.create') }}" class="btn btn-primary" style="margin-bottom: 1rem;">Create User</a>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
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
                                <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" onclick="return confirm('Delete user?')">Delete</button>
                                </form>
                            @endif
                            @if(auth()->user() && (auth()->user()->isManager() || auth()->user()->isAdmin()))
                                <form action="{{ route('users.sendReset', $user) }}" method="POST" style="display:inline-block; margin-left: 0.5rem;">
                                    @csrf
                                    <button class="btn btn-primary">Send Reset</button>
                                </form>
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
