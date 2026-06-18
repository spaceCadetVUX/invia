<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserNotBanned
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (auth()->check() && auth()->user()->banned_at) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Tài khoản của bạn đã bị khóa. Liên hệ hỗ trợ.']);
        }

        return $next($request);
    }
}
