<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:staff,email',
        'password' => 'required|min:6',
    ]);

    $staff = Staff::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        // تعيين الصلاحيات المحددة هنا إجبارياً
        'permissions' => ['view_patients', 'manage_appointments'], 
        'is_active' => true
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'تم إنشاء حساب السكرتارية بصلاحيات محدودة',
        'staff' => $staff
    ], 201);
}
}