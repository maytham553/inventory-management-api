<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $types  One or more allowed types, pipe separated (e.g. "Admin|SuperAdmin").
     */
    public function handle(Request $request, Closure $next, $types)
    {
        $user = auth('sanctum')->user();
        $allowedTypes = explode('|', $types);

        if (! $user || ! in_array($user->type, $allowedTypes, true)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return $next($request);
    }
}
