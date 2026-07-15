<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

#[Layout('layouts.auth')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required|string',
    ];

    public function login()
    {
        $this->validate();

        // 🔒 SECURITY FIX: Rate limiting (ISSUE-011)
        $key = 'login:' . request()->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik."
            ]);
        }

        if ($this->remember) {
            // Session di-set untuk durasi lebih panjang via attempt()
        }

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // 🔒 SECURITY FIX: Clear rate limiter on success (ISSUE-011)
            RateLimiter::clear($key);
            
            // 🔒 SECURITY FIX: Session regeneration (ISSUE-011)
            request()->session()->regenerate();
            request()->session()->regenerateToken();
            
            // 🔒 SECURITY FIX: Audit log (ISSUE-013)
            Log::info('User logged in', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $user = Auth::user();
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($user->isSeller()) {
                // Seller bisa login regardless of status (pending, approved, rejected)
                return redirect()->intended(route('seller.dashboard'));
            } else {
                return redirect()->intended(route('home'));
            }
        }

        // 🔒 SECURITY FIX: Increment rate limiter on failure (ISSUE-011)
        RateLimiter::hit($key, 60);
        
        session()->flash('error', 'Email atau kata sandi salah.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
