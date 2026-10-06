<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'กรุณากรอกรหัสพนักงานหรืออีเมล',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
        ]);

        $key = mb_strtolower($validated['login']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'login' => "พยายามเข้าสู่ระบบหลายครั้งเกินไป กรุณารอ {$seconds} วินาที",
            ]);
        }

        $identifier = filter_var($validated['login'], FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'employee_code';

        if (! Auth::attempt([
            $identifier => $validated['login'],
            'password' => $validated['password'],
            'status' => 'active',
        ])) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'login' => 'รหัสพนักงาน/อีเมล หรือรหัสผ่านไม่ถูกต้อง หรือบัญชีถูกระงับ',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended('/');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
