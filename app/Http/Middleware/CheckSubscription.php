<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

        // 1. السماح للسوبر أدمن دائماً
        if ($user instanceof \App\Models\User && $user->role === 'super_admin') {
            return $next($request);
        }

        // 2. إذا كان المستخدم من طاقم العمل (Staff)
        if ($user instanceof \App\Models\Staff) {
            if (!$user->is_active) {
                return response()->json(['error' => 'حساب الموظف معطل حالياً'], 403);
            }
            // السكرتارية تتبع اشتراك الطبيب (هنا نفترض أنها مرتبطة بطبيب عبر علاقة)
            // إذا لم يكن هناك ربط مباشر، نعتبرها نشطة طالما حسابها نشط
            return $next($request);
        }

        // 3. فحص اشتراك الطبيب (User)
        // نبحث عن اشتراك "نشط" لم تنتهِ صلاحيته بعد
        $subscription = $user->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'error' => 'انتهت صلاحية اشتراك العيادة. يرجى التجديد للمتابعة.',
                'subscription_status' => 'expired'
            ], 403);
        }

        return $next($request);
    }
}