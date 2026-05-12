<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabOrder;
use Illuminate\Http\Request;

class LabOrderController extends Controller
{
    public function index()
    {
        // عرض الطلبيات مع بيانات المريض المرتبط بها
        return response()->json(LabOrder::with('patient')->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'lab_name' => 'required|string',
            'work_type' => 'required|string', // مثال: تاج زيركون
            'tooth_number' => 'nullable|integer',
            'cost' => 'required|numeric',
            'sent_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:sent_date',
        ]);

        $order = LabOrder::create($validated);
        return response()->json(['message' => 'تم تسجيل طلب المختبر', 'order' => $order]);
    }

    public function updateStatus(Request $request, LabOrder $order)
    {
        $request->validate(['status' => 'required|in:ordered,received,fitted']);
        
        $order->update(['status' => $request->status]);
        return response()->json(['message' => 'تم تحديث حالة الطلب بنجاح']);
    }
}