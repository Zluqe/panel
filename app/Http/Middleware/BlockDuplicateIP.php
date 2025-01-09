<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class BlockDuplicateIP
{
    public function handle($request, Closure $next)
    {
        $ip = $request->ip();
        $userCount = User::where('ip_address', $ip)->count();

        if ($userCount > 1) {
            return response()->json(['error' => 'Multiple accounts detected for this IP.'], 403);
        }

        return $next($request);
    }
}
