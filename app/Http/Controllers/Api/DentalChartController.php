<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DentalChartResource;
use App\Models\DentalChart;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DentalChartController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id'   => 'required|exists:patients,id',
            'tooth_number' => 'required|integer',
            'procedure_id' => 'nullable|exists:procedures,id', 
            'final_price'  => 'required|numeric|min:0',
            'paid_now'     => 'nullable|numeric|min:0',
            'doctor_notes' => 'nullable|string',
            'currency'     => 'required|string|in:USD,SYP', // تم التعديل من SYR إلى SYP لتطابق React
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                // 1. تسجيل المعالجة في مخطط الأسنان
                $chart = DentalChart::create([
                    'patient_id'   => $validated['patient_id'],
                    'procedure_id' => $validated['procedure_id'],
                    'tooth_number' => $validated['tooth_number'],
                    'final_price'  => $validated['final_price'],
                    'paid_now'     => $validated['paid_now'] ?? 0,
                    'currency'     => $validated['currency'],
                    'doctor_notes' => $validated['doctor_notes'] ?? 'معالجة عامة',
                    'state'        => 'completed',
                ]);

                // 2. جلب أو إنشاء فاتورة "غير مدفوعة" بنفس العملة
                $invoice = Invoice::firstOrCreate(
                    [
                        'patient_id' => $validated['patient_id'], 
                        'currency'   => $validated['currency'], 
                        'status'     => 'unpaid'
                    ],
                    ['total_amount' => 0, 'paid_amount' => 0]
                );

                // 3. تحديث مبالغ الفاتورة
                $invoice->total_amount += (float)$validated['final_price'];
                $invoice->paid_amount += (float)($validated['paid_now'] ?? 0);
                
                // تحديث الحالة والمتبقي
                $invoice->updateBalances(); 

                return response()->json([
                    'status' => 'success', 
                    'message' => 'تم حفظ المعالجة وتحديث الفاتورة بنجاح',
                    'data' => $chart->load('procedure')
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطأ في النظام: ' . $e->getMessage()], 500);
        }
    }

    public function getPatientHistory($patientId)
    {
        $history = DentalChart::where('patient_id', $patientId)
            ->with('procedure')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return DentalChartResource::collection($history);
    }
}