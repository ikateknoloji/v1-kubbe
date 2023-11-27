<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckIfPasswordIsTemporary
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_temp_password) {
            return response()->json([
                'is_temp_password' => true,
                'message' => 'Geçici şifrenizi değiştirmeniz gerekiyor.'
            ], 200);
        }

        return $next($request);
    }
}