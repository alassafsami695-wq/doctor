<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray($request)
    {
        // ← تأكد أن dentalHistory محملة
        $dentalHistory = $this->dentalHistory ?? collect();

        $balances = $dentalHistory->groupBy('currency')->map(function ($group) {
            $paid = $group->sum(function($item) {
                return (float) ($item->paid_now ?? 0);
            });
            $price = $group->sum(function($item) {
                return (float) ($item->final_price ?? 0);
            });
            return $paid - $price;
        });

        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'panorama_image' => $this->panorama_image ? url('storage/' . $this->panorama_image) : null,
            'medical_history' => $this->medical_history ?? '',
            'allergies' => $this->allergies ?? '',
            'current_medications' => $this->current_medications ?? '',
            
            // ← الأرصدة مع التأكد من القيم
            'balances' => [
                'USD' => (float) ($balances->get('USD') ?? 0),
                'SYP' => (float) ($balances->get('SYP') ?? 0), 
            ],

            'dental_history' => $dentalHistory->map(function ($item) {
                $paid = (float) ($item->paid_now ?? 0);
                $price = (float) ($item->final_price ?? 0);
                
                return [
                    'id' => $item->id,
                    'tooth_number' => $item->tooth_number,
                    'doctor_notes' => $item->doctor_notes ?? '',
                    'final_price' => $price,
                    'paid_now' => $paid,
                    'balance' => $paid - $price,
                    'currency' => $item->currency ?? 'SYP',
                    'procedure_name' => $item->procedure ? $item->procedure->name : 'معالجة عامة',
                    'created_at' => $item->created_at ? $item->created_at->format('Y/m/d') : '',
                ];
            }),

            'dental_charts_summary' => $dentalHistory->reduce(function ($carry, $item) {
                $carry[$item->tooth_number] = $item->procedure ? $item->procedure->slug : 'other';
                return $carry;
            }, []),
        ];
    }
}