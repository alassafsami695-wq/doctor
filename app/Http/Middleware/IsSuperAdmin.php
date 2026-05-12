<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->role === 'super_admin') {
            return $next($request);
        }
        return response()->json(['error' => 'عذراً، هذه الصلاحية للمدير العام فقط'], 403);
    }

    
}
