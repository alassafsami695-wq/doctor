<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    // All available permissions with labels
    private const AVAILABLE_PERMISSIONS = [
        'view_patients' => 'عرض المرضى',
        'add_patients' => 'إضافة مرضى',
        'edit_patients' => 'تعديل بيانات المرضى',
        'delete_patients' => 'حذف المرضى',
        'manage_appointments' => 'إدارة المواعيد',
        'view_invoices' => 'عرض الفواتير',
        'create_invoices' => 'إنشاء فواتير',
        'manage_payments' => 'إدارة المدفوعات',
        'view_expenses' => 'عرض المصاريف',
        'manage_expenses' => 'إدارة المصاريف',
        'view_reports' => 'عرض التقارير',
        'manage_staff' => 'إدارة الموظفين',
        'manage_settings' => 'إدارة الإعدادات',
    ];

    /**
     * Get all available permissions (for frontend)
     */
    public function permissions()
    {
        return response()->json([
            'status' => 'success',
            'permissions' => self::AVAILABLE_PERMISSIONS
        ]);
    }

    /**
     * List all staff members
     */
    public function index()
    {
        $staff = Staff::all()->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'permissions' => $member->permissions ?? [],
                'is_active' => $member->is_active,
                'created_at' => $member->created_at,
            ];
        });

        return response()->json([
            'status' => 'success',
            'staff' => $staff
        ]);
    }

    /**
     * Create new staff with selected permissions
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email',
            'password' => 'required|min:6',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|in:' . implode(',', array_keys(self::AVAILABLE_PERMISSIONS)),
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
            'permissions.required' => 'يجب اختيار صلاحية واحدة على الأقل',
            'permissions.array' => 'الصلاحيات يجب أن تكون مصفوفة',
            'permissions.*.in' => 'إحدى الصلاحيات المختارة غير صالحة',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ في التحقق',
                'errors' => $validated->errors()
            ], 422);
        }

        $data = $validated->validated();

        $staff = Staff::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'permissions' => $data['permissions'], // ✅ Save selected permissions
            'is_active' => true
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء حساب الموظف بنجاح',
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'permissions' => $staff->permissions,
                'is_active' => $staff->is_active,
            ]
        ], 201);
    }

    /**
     * Update staff permissions
     */
    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:staff,email,' . $id,
            'permissions' => 'sometimes|array|min:1',
            'permissions.*' => 'string|in:' . implode(',', array_keys(self::AVAILABLE_PERMISSIONS)),
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validated->errors()
            ], 422);
        }

        $data = $validated->validated();

        if (isset($data['permissions'])) {
            $staff->permissions = $data['permissions'];
        }
        if (isset($data['name'])) {
            $staff->name = $data['name'];
        }
        if (isset($data['email'])) {
            $staff->email = $data['email'];
        }
        if (isset($data['is_active'])) {
            $staff->is_active = $data['is_active'];
        }

        $staff->save();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث بيانات الموظف',
            'staff' => $staff
        ]);
    }

    /**
     * Delete staff member
     */
    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف الموظف'
        ]);
    }

    public function getSuperAdminStats()
    {
        // حساب الاشتراكات النشطة فعلياً (حالة نشطة وتاريخ لم ينتهِ)
        $activeSubsCount = \App\Models\Subscription::where('status', 'active')
            ->where('ends_at', '>', now())
            ->count();

        // إجمالي المبالغ المحصلة من جدول الاشتراكات
        $revenue = \App\Models\Subscription::sum('price');

        return response()->json([
            'total_clinics' => \App\Models\User::where('role', 'dentist')->count(),
            'active_subs'   => $activeSubsCount,
            'expired_subs'  => \App\Models\Subscription::where('ends_at', '<=', now())->count(),
            'total_revenue' => number_format($revenue, 0, '.', ','), // تنسيق الرقم بفاصلة آلاف
            'latest_clinics'=> \App\Models\User::where('role', 'dentist')
                                ->with('subscription') // تحميل العلاقة
                                ->latest()
                                ->take(5)
                                ->get(),
        ]);
    }
}