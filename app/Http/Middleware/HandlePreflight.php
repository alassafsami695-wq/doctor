<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandlePreflight
{
    public function handle(Request $request, Closure $next)
    {
        // ✅ الرد المباشر على طلبات OPTIONS
        if ($request->getMethod() === 'OPTIONS') {
            return response()->json('OK', 200, [
                'Access-Control-Allow-Origin' => $request->header('Origin') ?? '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '86400',
            ]);
        }

        $response = $next($request);
        
        // ✅ إضافة Headers للردود العادية أيضاً
        $response->headers->set('Access-Control-Allow-Origin', $request->header('Origin') ?? '*');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        
        return $response;
    }
}