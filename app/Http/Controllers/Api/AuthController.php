<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Staff;
use App\Models\DoctorCode;

class AuthController extends Controller
{
    // ==================== REGISTER (تسجيل جديد برمز الأدمن) ====================
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'required|string|max:20',
            'clinic_address' => 'nullable|string|max:255',
            'doctor_code' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // ✅ التحقق من رمز الأدمن
        $code = DoctorCode::valid()->where('code', $request->doctor_code)->first();

        if (!$code) {
            return response()->json([
                'message' => 'رمز التسجيل غير صالح أو منتهي الصلاحية أو مستخدم مسبقاً'
            ], 400);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'clinic_address' => $request->clinic_address,
                'role' => 'dentist',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // ✅ تعليم الرمز كمستخدم
            $code->update(['is_used' => true]);

            // ✅ إنشاء اشتراك تجريبي
            try {
                $user->subscriptions()->create([
                    'starts_at' => now(),
                    'ends_at' => now()->addDays(14),
                    'status' => 'trial',
                    'months_duration' => 0,
                    'price' => 0,
                    'clinic_id' => null,
                    'notes' => 'حساب تجريبي مبدئي'
                ]);
            } catch (\Exception $e) {
                Log::error('Subscription Creation Error: ' . $e->getMessage());
            }

            return $this->generateAuthResponse($user, 'dentist');

        } catch (\Exception $e) {
            Log::error('Registration Database Error: ' . $e->getMessage());
            return response()->json(['message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()], 500);
        }
    }

    // ==================== LOGIN (تسجيل الدخول) ====================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            if (!$user->is_active) {
                return response()->json([
                    'message' => 'الحساب معطل',
                ], 403);
            }
            return $this->generateAuthResponse($user, $user->role);
        }

        $staff = Staff::where('email', $request->email)->first();
        if ($staff && Hash::check($request->password, $staff->password)) {
            if (isset($staff->is_active) && !$staff->is_active) {
                return response()->json(['message' => 'الحساب معطل'], 403);
            }
            return $this->generateAuthResponse($staff, 'staff');
        }

        return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
    }

    // ==================== LOGOUT (تسجيل الخروج) ====================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    // ==================== PRIVATE METHODS ====================
    private function generateAuthResponse($user, $role)
    {
        try {
            $user->tokens()->delete();
        } catch (\Exception $e) {
            Log::info('No initial tokens to delete.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $permissions = match($role) {
            'super_admin' => ['all', 'manage_clinics', 'view_logs'],
            'doctor', 'dentist' => ['all', 'view_financials', 'view_settings'],
            'receptionist', 'staff' => ['view_patients', 'add_patients', 'manage_appointments'],
            default => []
        };

        $hasActiveSub = false;
        try {
            $hasActiveSub = $user->has_active_subscription;
        } catch (\Exception $e) {
            $hasActiveSub = true; 
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $role,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role,
                'permissions' => $permissions,
            ],
            'has_active_subscription' => $hasActiveSub,
        ]);
    }
}