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
     */
    public function handle(Request $request, Closure $next, $type)
    {
       
        $userType = auth('sanctum')->user()->type;

        if ($type == 'SuperAdmin' && $userType != 'SuperAdmin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if ($type == 'Admin' && $userType != 'Admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if ($type == 'User' && $userType != 'User'  ) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return $next($request);
    }
}
