<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Http\Resources\AppointmentResource;
use App\Http\Requests\StoreAppointmentRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * جلب المواعيد مع دعم الفلترة لمواعيد اليوم
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor']);

        if ($request->filled('filter') && $request->filter === 'today') {
            $query->whereDate('appointment_date', Carbon::today());
        }

        $appointments = $query->orderBy('appointment_date', 'asc')->get();
        
        // استخدام الـ Resource يضمن وصول patient_id للفرونت إند
        return AppointmentResource::collection($appointments);
    }
    /**
     * حفظ موعد جديد
     */
    public function store(StoreAppointmentRequest $request)
    {
        // البحث عن المريض بالاسم أو إنشاؤه
        $patient = \App\Models\Patient::firstOrCreate(
            ['full_name' => $request->patient_name]
        );

        $appointment = Appointment::create([
            'patient_id'       => $patient->id,
            'user_id'          => auth()->id() ?? 1,
            'appointment_date' => $request->appointment_date,
            'notes'            => $request->notes,
            'status'           => $request->status ?? 'pending',
            'duration'         => $request->duration ?? 30,
        ]);

        return new AppointmentResource($appointment->load(['patient', 'doctor']));
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $appointment->update(['status' => $request->status]);
        return response()->json(['message' => 'تم تحديث حالة الموعد بنجاح']);
    }
}