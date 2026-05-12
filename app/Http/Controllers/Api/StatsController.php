<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function getClinicStats() {
        try {
            return response()->json([
                'total_patients' => Patient::count(),
                'today_appointments' => Appointment::whereDate('appointment_date', today())->count(),
                'ongoing_cases' => Invoice::where('remaining_amount', '>', 0)->count(),
                'growth_rate' => '+12%', // قيمة افتراضية حالياً
                'alerts' => [
                    'low_stock' => Inventory::whereColumn('quantity', '<=', 'low_stock_threshold')->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطأ في جلب البيانات'], 500);
        }
    }

    public function getSuperAdminStats()
    {
        // إحصائيات للأدمن العام عن العيادات المشتركة
        return response()->json([
            'active_subscriptions' => \App\Models\Subscription::where('status', 'active')->count(),
            'total_clinics' => \App\Models\User::where('role', 'dentist')->count(),
            'expiring_soon' => \App\Models\Subscription::where('ends_at', '<', now()->addDays(7))->get(),
        ]);
    }
}