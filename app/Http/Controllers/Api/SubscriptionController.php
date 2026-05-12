<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment; // تأكد من استدعاء موديل الدفع
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function activate(Request $request)
    {
        // 1. التحقق من البيانات المرسلة
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'months'  => 'required|integer|min:1',
            'price'   => 'required|numeric|min:0', // أضفنا حقل السعر لزيادة الرصيد
        ]);

        // استخدام Transaction لضمان تنفيذ العمليتين معاً (الاشتراك والدفع)
        return DB::transaction(function () use ($request) {
            
            $startsAt = Carbon::now();
            $endsAt = Carbon::now()->addMonths($request->months);

            // 2. إنشاء أو تحديث الاشتراك
            $subscription = Subscription::updateOrCreate(
                ['user_id' => $request->user_id],
                [
                    'starts_at'       => $startsAt,
                    'ends_at'         => $endsAt,
                    'months_duration' => $request->months,
                    'status'          => 'active'
                ]
            );

            // 3. إنشاء سجل دفع (هنا يزداد رصيد الآدمن)
            // نربط الدفعة بـ subscription_id لتميزها عن فواتير المرضى
            Payment::create([
                'subscription_id' => $subscription->id,
                'amount'          => $request->price,
                'payment_method'  => 'admin_activation', // تفعيل يدوي من الآدمن
                'payment_date'    => Carbon::now(),
                'invoice_id'      => null, // اتركها فارغة لأنها ليست فاتورة مريض
            ]);

            return response()->json([
                'message'    => 'تم تفعيل الاشتراك وزيادة رصيد الآدمن بنجاح',
                'expires_at' => $endsAt->toDateTimeString(),
                'amount_added' => $request->price
            ]);
        });
    }
}