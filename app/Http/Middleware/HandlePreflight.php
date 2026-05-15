<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandlePreflight
{
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');

        // ✅ قائمة الـ Origins المسموح بها (يجب مطابقة config/cors.php)
        $allowedOrigins = [
            'https://peppy-faun-569f3f.netlify.app',
            'http://localhost:3000',
            'http://localhost:5173',
        ];

        // ✅ إذا لم يكن Origin في القائمة، استخدم null (ليس *)
        $allowedOrigin = in_array($origin, $allowedOrigins) ? $origin : null;

        // ✅ الرد المباشر على طلبات OPTIONS
        if ($request->getMethod() === 'OPTIONS') {
            return response()->json('OK', 200, [
                'Access-Control-Allow-Origin' => $allowedOrigin,
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept, Origin',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '86400',
            ]);
        }

        $response = $next($request);
        
        // ✅ إضافة Headers للردود العادية
        if ($allowedOrigin) {
            $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
        }
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        
        return $response;
    }
}