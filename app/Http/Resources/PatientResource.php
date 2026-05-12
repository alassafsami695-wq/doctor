<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray($request)
{
    // حساب الأرصدة: (ما دفعه المريض - السعر النهائي المتفق عليه)
    // إذا دفع 1000 والسعر 100 -> النتيجة +900 (رصيد للمريض)
    // إذا دفع 50 والسعر 100 -> النتيجة -50 (دين على المريض)
    $balances = $this->dentalHistory->groupBy('currency')->map(function ($group) {
        return $group->sum('paid_now') - $group->sum('final_price');
    });

    return [
        'id'                  => $this->id,
        'full_name'           => $this->full_name,
        'phone'               => $this->phone,
        'panorama_image'      => $this->panorama_image ? url('storage/' . $this->panorama_image) : null,
        'current_medications' => $this->current_medications ?? 'لا يوجد',
        'allergies'           => $this->allergies ?? 'لا يوجد',
        'medical_history'     => $this->medical_history ?? 'لا يوجد سوابق',
        
        // الأرصدة المعدلة
        'balances' => [
            'USD' => (float) ($balances->get('USD') ?? 0),
            'SYP' => (float) ($balances->get('SYP') ?? 0), 
        ],

        'dental_history' => $this->dentalHistory->map(function ($item) {
            // حساب رصيد كل حركة على حدة لتسهيل العرض في الواجهة
            $itemBalance = (float)$item->paid_now - (float)$item->final_price;
            
            return [
                'id'             => $item->id,
                'tooth_number'   => $item->tooth_number,
                'doctor_notes'   => $item->doctor_notes ?? '',
                'final_price'    => (float) $item->final_price,
                'paid_now'       => (float) $item->paid_now,
                'balance'        => $itemBalance, // رصيد هذه العملية
                'currency'       => $item->currency,
                'procedure_name' => $item->procedure ? $item->procedure->name : 'معالجة عامة',
                'created_at'     => $item->created_at->format('Y/m/d'),
            ];
        }),

        'dental_charts_summary' => $this->dentalHistory->reduce(function ($carry, $item) {
            $carry[$item->tooth_number] = $item->procedure ? $item->procedure->slug : 'other';
            return $carry;
        }, []),
    ];
}
}