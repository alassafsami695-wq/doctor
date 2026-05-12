<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Staff;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $password = $request->password;

        // 1. البحث في جدول المستخدمين (أطباء وآدمن)
        $user = User::where('email', $email)->first();
        if ($user && Hash::check($password, $user->password)) {
            // تحديد الرتبة بناءً على حقل في قاعدة البيانات (افترضنا وجود حقل role)
            $role = ($user->email === 'admin@clinic.com') ? 'super_admin' : 'doctor';
            return $this->generateAuthResponse($user, $role);
        }

        // 2. البحث في جدول السكرتارية
        $staff = Staff::where('email', $email)->first();
        if ($staff && Hash::check($password, $staff->password)) {
            if (isset($staff->is_active) && !$staff->is_active) {
                return response()->json(['message' => 'الحساب معطل'], 403);
            }
            return $this->generateAuthResponse($staff, 'staff');
        }

        return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
    }

    private function generateAuthResponse($user, $role)
    {
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        // تحديد المصفوفة بناءً على الرتبة
        $permissions = match($role) {
            'super_admin' => ['all', 'manage_clinics', 'view_logs'],
            'doctor'      => ['all', 'view_financials', 'view_settings'],
            'staff'       => ['view_patients', 'add_patients', 'manage_appointments'],
            default       => []
        };

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $role,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'permissions' => $permissions,
            ],
            'has_active_subscription' => true,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }
}