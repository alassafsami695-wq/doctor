<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /**
     * تفعيل أو تجديد الاشتراك
     */
    public function activate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'months'  => 'required|integer|min:1',
            'price'   => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            
            $existingSubscription = Subscription::where('user_id', $request->user_id)->first();

            if ($existingSubscription && $existingSubscription->isActive()) {
                // تمديد الاشتراك الحالي
                $startsAt = Carbon::parse($existingSubscription->starts_at);
                $endsAt = Carbon::parse($existingSubscription->ends_at)->addMonths($request->months);
                $totalMonths = $existingSubscription->months_duration + $request->months;
            } else {
                // اشتراك جديد أو منتهي
                $startsAt = Carbon::now();
                $endsAt = Carbon::now()->addMonths($request->months);
                $totalMonths = $request->months;
            }

            $subscription = Subscription::updateOrCreate(
                ['user_id' => $request->user_id],
                [
                    'starts_at'       => $startsAt,
                    'ends_at'         => $endsAt,
                    'months_duration' => $totalMonths,
                    'price'           => $request->price,
                    'status'          => 'active'
                ]
            );

            Payment::create([
                'subscription_id' => $subscription->id,
                'amount'          => $request->price,
                'payment_method'  => 'admin_activation',
                'payment_date'    => Carbon::now(),
                'invoice_id'      => null,
            ]);

            return response()->json([
                'message'      => 'تم تفعيل الاشتراك وزيادة الرصيد بنجاح',
                'expires_at'   => $endsAt->toDateTimeString(),
                'amount_added' => $request->price
            ]);
        });
    }

    /**
     * التحقق من حالة الاشتراك
     */
    public function checkStatus($userId)
    {
        $subscription = Subscription::where('user_id', $userId)->first();

        if (!$subscription) {
            return response()->json([
                'status' => 'none',
                'message' => 'لا يوجد اشتراك'
            ]);
        }

        return response()->json([
            'status'     => $subscription->isActive() ? 'active' : 'expired',
            'starts_at'  => $subscription->starts_at,
            'ends_at'    => $subscription->ends_at,
            'days_left'  => $subscription->isActive() ? now()->diffInDays($subscription->ends_at) : 0
        ]);
    }
}