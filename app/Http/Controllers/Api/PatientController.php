<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Resources\PatientResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    /**
     * جلب قائمة المرضى مع إمكانية البحث
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = Patient::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // تحميل العلاقات مع عد السجلات الطبية
        $patients = $query->withCount('dentalHistory')->latest()->get(); 

        return PatientResource::collection($patients);
    }

    /**
     * عرض بيانات مريض محدد مع كامل تاريخه الطبي
     */
    public function show($id)
    {
        $patient = Patient::with([
            'dentalHistory.procedure', 
            'appointments', 
            'invoices'
        ])->find($id);

        if (!$patient) {
            return response()->json(['message' => 'المريض غير موجود'], 404);
        }

        return new PatientResource($patient);
    }

    /**
     * إضافة مريض جديد
     */
    public function store(StorePatientRequest $request)
    {
        $patient = Patient::create($request->validated());
        return new PatientResource($patient);
    }

    /**
     * تحديث بيانات المريض العامة وصورته
     */
    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('panorama_image')) {
            // حذف الصورة القديمة لتحسين المساحة في MySQL/Storage
            if ($patient->panorama_image && Storage::disk('public')->exists($patient->panorama_image)) {
                Storage::disk('public')->delete($patient->panorama_image);
            }
            $data['panorama_image'] = $request->file('panorama_image')->store('panorama', 'public');
        }

        $patient->update($data);
        return new PatientResource($patient);
    }

    /**
     * حذف سجل المريض
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(['message' => 'تم حذف سجل المريض بنجاح']);
    }

    /**
     * الدالة الأساسية لتحديث السجل الطبي ورفع صورة الأشعة (Panorama)
     */
    public function updateInfo(Request $request, $id = null)
{
    $patientId = $id ?: $request->patient_id;
    $patient = Patient::findOrFail($patientId);

    // معالجة الصورة إذا وجدت
    if ($request->hasFile('panorama_image')) {
        // حذف القديمة
        if ($patient->panorama_image && Storage::disk('public')->exists($patient->panorama_image)) {
            Storage::disk('public')->delete($patient->panorama_image);
        }
        
        // تخزين الجديدة
        $path = $request->file('panorama_image')->store('panorama', 'public');
        $patient->panorama_image = $path;
    }

    // تحديث البيانات النصية فقط إذا كانت موجودة في الطلب
    $patient->update($request->only(['medical_history', 'allergies', 'current_medications']));
    $patient->save();

    return response()->json([
        'message' => 'تم تحديث البيانات بنجاح',
        'image_url' => $patient->panorama_image ? asset('storage/' . $patient->panorama_image) : null,
        'patient' => $patient
    ]);
}
}