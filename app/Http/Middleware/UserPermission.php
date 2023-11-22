<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UserPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,...$allowedTypes): Response
    {
        $user = Auth::user();

        if ($user && in_array($user->user_type, $allowedTypes)) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized. You do not have permission.'], 403);
    }
}
