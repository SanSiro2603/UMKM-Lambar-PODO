<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->role === 'seller' && $user->store?->status === 'suspended') {
            $suspensionReason = trim((string) $user->store->suspension_reason);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('seller_suspended', [
                    'title' => 'Akun Seller Dinonaktifkan',
                    'reason' => $suspensionReason !== ''
                        ? $suspensionReason
                        : 'Tidak ada alasan tambahan dari admin.',
                ]);
        }

        return $next($request);
    }
}
