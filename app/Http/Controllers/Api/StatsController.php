<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Payment;
use Auth;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    // --- إحصائيات العيادة (للطبيب) ---
    public function getClinicStats() {
        try {
            $doctorId = Auth::id();

            return response()->json([
                // ✅ مرضى هذا الطبيب فقط
                'total_patients' => Patient::where('doctor_id', $doctorId)->count(),
                
                // ✅ مواعيد اليوم لهذا الطبيب فقط
                'today_appointments' => Appointment::where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', today())
                    ->count(),
                
                // ✅ الحالات النشطة (فواتير غير مسددة لهذا الطبيب)
                'ongoing_cases' => Invoice::where('doctor_id', $doctorId)
                    ->where('remaining_amount', '>', 0)
                    ->count(),
                
                // ✅ إجمالي الفواتير لهذا الطبيب
                'total_invoices' => Invoice::where('doctor_id', $doctorId)->count(),
                
                'growth_rate' => '+12%',
                
                'alerts' => [
                    // ✅ المخزن الخاص بهذه العيادة/الطبيب
                    'low_stock' => Inventory::where('doctor_id', $doctorId)
                        ->whereColumn('quantity', '<=', 'low_stock_threshold')
                        ->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطأ في جلب البيانات: ' . $e->getMessage()], 500);
        }
    }

    // --- إحصائيات السوبر أدمن (للعرض في Dashboard) ---
    public function getSuperAdminStats()
    {
        $now = now();
        $sevenDaysLater = $now->copy()->addDays(7);

        // الاشتراكات التي ستنتهي خلال 7 أيام
        $expiringSoon = Subscription::where('status', 'active')
            ->where('ends_at', '<', $sevenDaysLater)
            ->where('ends_at', '>', $now)
            ->with('user:id,name,email,phone,is_active')
            ->get();

        // إحصائيات عامة
        $totalClinics = User::where('role', 'dentist')->count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $expiredSubscriptions = Subscription::where('status', 'expired')->count();
        
        // الإيرادات من الاشتراكات
        $totalRevenue = Payment::whereNotNull('subscription_id')->sum('amount');

        // كل الاشتراكات لصفحة الاشتراكات
        $allSubscriptions = Subscription::with('user:id,name,email')->get();

        return response()->json([
            // ✅ توافق مباشر مع AdminDashboardPage
            'total_clinics' => $totalClinics,
            'active_subs' => $activeSubscriptions,
            'expired_subs' => $expiredSubscriptions,
            'total_revenue' => $totalRevenue,
            
            'latest_clinics' => User::where('role', 'dentist')
                ->latest()
                ->take(5)
                ->with('subscription')
                ->get(),
            
            // ✅ توافق مع AdminSubscriptionsPage
            'all_subscriptions' => $allSubscriptions,
            'expiring_soon' => $expiringSoon,
        ]);
    }
}