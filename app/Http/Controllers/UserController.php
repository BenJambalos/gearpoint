<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $users = User::paginate(20);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);
        return view('users.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,manager,cashier,mechanic',
        ]);

        // If creator is a manager, allow only creating cashiers
        if (Auth::user() && Auth::user()->isManager() && $data['role'] !== 'cashier') {
            return redirect()->back()->withErrors(['role' => 'Managers can only create cashiers.']);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'User created');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,manager,cashier,mechanic',
        ]);

        // If actor is manager, prevent changing role to non-cashier
        if (Auth::user() && Auth::user()->isManager() && $data['role'] !== 'cashier') {
            return redirect()->back()->withErrors(['role' => 'Managers can only edit cashier roles.']);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->role = $data['role'];
        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted');
    }

    public function sendReset(User $user)
    {
        $this->authorize('update', $user);
        $status = Password::sendResetLink(['email' => $user->email]);
        return redirect()->route('users.index')->with('success', 'Password reset link sent.');
    }
}
