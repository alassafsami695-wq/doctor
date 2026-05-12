<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function getStats()
    {
        // 1. عدد العيادات (المستخدمين برتبة طبيب)
        $totalClinics = User::where('role', 'dentist')->count();

        // 2. حالات الاشتراكات
        $activeSubs = Subscription::where('status', 'active')->count();
        $expiredSubs = Subscription::where('status', 'expired')->count();

        // 3. حساب رصيد الآدمن الصافي من الاشتراكات فقط
        // نجمع مبالغ الدفع المرتبطة بـ subscription_id فقط
        $adminRevenue = Payment::whereNotNull('subscription_id')->sum('amount');

        // 4. جلب آخر العيادات المنضمة
        $latestClinics = User::where('role', 'dentist')
            ->with('subscription')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'total_clinics' => $totalClinics,
            'active_subs' => $activeSubs,
            'expired_subs' => $expiredSubs,
            'total_revenue' => number_format($adminRevenue, 0, '.', ','),
            'latest_clinics' => $latestClinics
        ]);
    }
}