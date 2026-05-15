<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'غير مصرح بالدخول'], 401);
        }

        // 1. السماح للسوبر أدمن دائماً دون قيود
        if ($user instanceof \App\Models\User && $user->role === 'super_admin') {
            return $next($request);
        }

        // 2. إذا كان المستخدم من طاقم العمل (Staff)
        if ($user instanceof \App\Models\Staff) {
            if (!$user->is_active) {
                return response()->json(['error' => 'حساب الموظف معطل حالياً'], 403);
            }
            return $next($request);
        }

        // 3. فحص اشتراك الطبيب (User) باستخدام الـ Accessor الآمن المربوط بالـ enum
        // سيعيد true إذا كان لدى الطبيب اشتراك تجريبي trial أو نشط active ولم ينتهِ بعد
        if (!$user->has_active_subscription) {
            return response()->json([
                'error' => 'انتهت صلاحية اشتراك العيادة أو لم يتم تفعيله. يرجى التجديد للمتابعة.',
                'subscription_status' => 'expired'
            ], 403);
        }

        return $next($request);
    }
}