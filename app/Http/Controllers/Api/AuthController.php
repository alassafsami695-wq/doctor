<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Staff;

class AuthController extends Controller
{
    // ==================== REGISTER (تسجيل جديد) ====================
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6', // ✅ أعدت confirmed
            'phone' => 'required|string|max:20',
            'clinic_address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 1. توليد كود التحقق (6 أرقام)
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // 2. إنشاء المستخدم (غير مفعل)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'clinic_address' => $request->clinic_address,
            'verification_code' => $verificationCode,
            'role' => 'dentist',
            'is_active' => false,
        ]);

        // 3. إرسال الكود عبر البريد
        try {
            Mail::raw("مرحباً دكتور {$user->name}،\n\nكود التحقق الخاص بك لتفعيل حساب Oravue هو: {$verificationCode}\n\nصالح لمدة 15 دقيقة.", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('🔐 كود تفعيل حساب Oravue');
            });
        } catch (\Exception $e) {
            \Log::error('Mail Error: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'تم إنشاء الحساب. الرجاء التحقق من بريدك الإلكتروني.',
            'email' => $user->email,
            'debug_code' => $verificationCode, // ⚠️ احذف هذا في الإنتاج!
        ], 201);
    }

    // ==================== VERIFY CODE (التحقق من الكود) ====================
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        if ($user->is_active) {
            return response()->json(['message' => 'الحساب مفعل مسبقاً'], 400);
        }

        // التحقق من الكود
        if ($user->verification_code === $request->code) {
            $user->update([
                'is_active' => true,
                'verification_code' => null,
                'email_verified_at' => now(),
            ]);

            // إنشاء اشتراك تجريبي 14 يوم
            $user->subscriptions()->create([
                'starts_at' => now(),
                'ends_at' => now()->addDays(14),
                'status' => 'active',
                'months_duration' => 0,
                'price' => 0,
            ]);

            return $this->generateAuthResponse($user, 'dentist');
        }

        return response()->json(['message' => 'كود التحقق غير صحيح'], 400);
    }

    // ==================== RESEND CODE (إعادة إرسال) ====================
    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        if ($user->is_active) {
            return response()->json(['message' => 'الحساب مفعل مسبقاً'], 400);
        }

        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update(['verification_code' => $verificationCode]);

        try {
            Mail::raw("مرحباً دكتور {$user->name}،\n\nكود التحقق الجديد لتفعيل حساب Oravue هو: {$verificationCode}\n\nصالح لمدة 15 دقيقة.", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('🔐 كود تفعيل حساب Oravue - إعادة إرسال');
            });
        } catch (\Exception $e) {
            \Log::error('Mail Error: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'تم إعادة إرسال كود التحقق',
            'debug_code' => $verificationCode, // ⚠️ احذف في الإنتاج
        ]);
    }

    // ==================== LOGIN (تسجيل الدخول) ====================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $password = $request->password;

        $user = User::where('email', $email)->first();
        if ($user && Hash::check($password, $user->password)) {
            if (!$user->is_active) {
                return response()->json([
                    'message' => 'الرجاء تفعيل الحساب من الإيميل أولاً',
                    'needs_verification' => true,
                    'email' => $user->email
                ], 403);
            }
            return $this->generateAuthResponse($user, $user->role);
        }

        $staff = Staff::where('email', $email)->first();
        if ($staff && Hash::check($password, $staff->password)) {
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
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        $permissions = match($role) {
            'super_admin' => ['all', 'manage_clinics', 'view_logs'],
            'doctor', 'dentist' => ['all', 'view_financials', 'view_settings'],
            'receptionist', 'staff' => ['view_patients', 'add_patients', 'manage_appointments'],
            default => []
        };

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
            'has_active_subscription' => $user->has_active_subscription ?? false,
        ]);
    }
}