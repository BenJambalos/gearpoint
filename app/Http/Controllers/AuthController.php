<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'regex:/^[A-Za-z0-9_]+$/'],
            'password' => ['required'],
        ], [
            'name.regex' => 'Name must not contain spaces or special characters. Use letters, numbers, or underscores only (e.g., AdminUser).',
        ]);

        $remember = false;
        if (Auth::attempt(['name' => $data['name'], 'password' => $data['password']], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'name' => 'Invalid credentials.',
        ])->withInput();
    }

    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function sendForgot(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found with that email.']);
        }

        // If using SMTP, ensure SMTP username and password are configured
        if (config('mail.default') === 'smtp') {
            $smtpUser = config('mail.mailers.smtp.username');
            $smtpPass = config('mail.mailers.smtp.password');
            // Strip surrounding quotes if present
            $smtpUser = trim($smtpUser ?? '', "\"'");
            // Basic validation: must contain an '@' and password must exist
            if (empty($smtpUser) || empty($smtpPass) || strpos($smtpUser, '@') === false || strtolower($smtpUser) === 'username') {
                return back()->withErrors(['email' => 'SMTP not configured. Please set MAIL_USERNAME (your email address) and MAIL_PASSWORD (app password or SMTP password) in your .env and clear config cache.']);
            }
        }

        try {
            $status = Password::sendResetLink(['email' => $user->email]);
            if ($status === Password::RESET_LINK_SENT) {
                \Log::info('Password reset link successfully sent', ['email' => $user->email]);
                return back()->with('success', 'Password reset link sent to the user email.');
            }
            \Log::warning('Password reset link failed to send', ['email' => $user->email, 'status' => $status]);
            return back()->withErrors(['email' => 'Unable to send password reset link.']);
        } catch (\Exception $e) {
            \Log::error('Failed sending password reset email: ' . $e->getMessage());
            $hint = '';
            // Provide a helpful hint for common local dev issue when mail host is 'mailpit'
            $mailHost = config('mail.mailers.smtp.host');
            $mailPort = config('mail.mailers.smtp.port');
            if (empty($mailHost) || strtolower($mailHost) === 'mailpit') {
                $hint = ' Check your MAIL_HOST setting (try 127.0.0.1 or localhost for local Mailpit).';
            }
            $message = config('app.debug') ? ('Unable to send password reset link: ' . $e->getMessage() . $hint . ' (mail: ' . ($mailHost ?? 'unknown') . ':' . ($mailPort ?? 'unknown') . ')') : ('Unable to send password reset link.' . $hint);
            return back()->withErrors(['email' => $message]);
        }
    }

    public function showResetForm(\Illuminate\Http\Request $request, $token)
    {
        $email = $request->query('email');
        return view('auth.reset', compact('token', 'email'));
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => 'required',
            'email' => 'sometimes|nullable|email',
            'name' => ['sometimes', 'nullable', 'string', 'regex:/^[A-Za-z0-9_]+$/'],
            'password' => 'required|confirmed|min:6',
        ], [
            'name.regex' => 'Name must not contain spaces or special characters.',
        ]);

        // If name provided instead of email, resolve to user's email
        if (empty($data['email']) && !empty($data['name'])) {
            $user = User::where('name', $data['name'])->first();
            if ($user) {
                $data['email'] = $user->email;
            }
        }

        $status = Password::reset($data, function ($user, $password) {
            $user->forceFill([
                'password' => bcrypt($password)
            ])->save();
        });

        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Password reset successful. Please login.');
        }
        return back()->withErrors(['email' => __($status)]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
