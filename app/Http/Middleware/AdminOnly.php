<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->get('is_admin')) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Log in as admin to do that.'], 401);
            }
            return redirect('/login');
        }

        return $next($request);
    }
}