<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorCode;

class DoctorCodeController extends Controller
{
    // إنشاء رمز جديد
    public function store(Request $request)
    {
        $request->validate([
            'expires_in_days' => 'required|integer|min:1|max:365',
        ]);

        $code = strtoupper(substr(md5(uniqid()), 0, 10));

        $doctorCode = DoctorCode::create([
            'code' => $code,
            'expires_at' => now()->addDays($request->expires_in_days),
        ]);

        return response()->json([
            'message' => 'تم إنشاء رمز التسجيل',
            'code' => $code,
            'expires_at' => $doctorCode->expires_at,
        ], 201);
    }

    // عرض جميع الرموز
    public function index()
    {
        $codes = DoctorCode::orderBy('created_at', 'desc')->get();
        return response()->json($codes);
    }

    // حذف رمز
    public function destroy($id)
    {
        DoctorCode::findOrFail($id)->delete();
        return response()->json(['message' => 'تم حذف الرمز']);
    }
}