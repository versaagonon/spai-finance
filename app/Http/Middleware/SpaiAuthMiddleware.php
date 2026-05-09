<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class SpaiAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Session::has('spai_logged_in')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
