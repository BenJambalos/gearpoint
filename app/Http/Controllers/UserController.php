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
        // If using SMTP, ensure SMTP username and password are configured
        if (config('mail.default') === 'smtp') {
            $smtpUser = config('mail.mailers.smtp.username');
            $smtpPass = config('mail.mailers.smtp.password');
            $smtpUser = trim($smtpUser ?? '', "\"'");
            if (empty($smtpUser) || empty($smtpPass) || strpos($smtpUser, '@') === false || strtolower($smtpUser) === 'username') {
                return redirect()->route('users.index')->withErrors(['send_reset' => 'SMTP not configured. Please set MAIL_USERNAME (email) and MAIL_PASSWORD (app password or SMTP password) in your .env and clear config cache.']);
            }
        }
        try {
            $status = Password::sendResetLink(['email' => $user->email]);
            if ($status === Password::RESET_LINK_SENT) {
                \Log::info('Admin triggered password reset link sent', ['email' => $user->email, 'by' => auth()->id()]);
                return redirect()->route('users.index')->with('success', 'Password reset link sent.');
            }
            \Log::warning('Admin password reset link could not be sent', ['email' => $user->email, 'status' => $status]);
            return redirect()->route('users.index')->withErrors(['send_reset' => 'Unable to send password reset link.']);
        } catch (\Exception $e) {
            \Log::error('Failed sending password reset (admin) email: ' . $e->getMessage());
            $hint = '';
            $mailHost = config('mail.mailers.smtp.host');
            $mailPort = config('mail.mailers.smtp.port');
            if (empty($mailHost) || strtolower($mailHost) === 'mailpit') {
                $hint = ' Check your MAIL_HOST setting (try 127.0.0.1 or localhost for local Mailpit).';
            }
            $message = config('app.debug') ? ('Unable to send password reset link: ' . $e->getMessage() . $hint . ' (mail: ' . ($mailHost ?? 'unknown') . ':' . ($mailPort ?? 'unknown') . ')') : ('Unable to send password reset link.' . $hint);
            return redirect()->route('users.index')->withErrors(['send_reset' => $message]);
        }
    }
}
