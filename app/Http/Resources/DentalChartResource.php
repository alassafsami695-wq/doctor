<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DentalChartResource extends JsonResource
{
    public function toArray($request): array {
    $balance = (float)($this->paid_now ?? 0) - (float)($this->final_price ?? 0);
    return [
        'id'             => $this->id,
        'tooth_number'   => $this->tooth_number,
        'doctor_notes'   => $this->doctor_notes ?? '',
        'procedure_name' => $this->procedure?->name ?? 'إجراء عام',
        'final_price'    => (float) ($this->final_price ?? 0), 
        'paid_now'       => (float) ($this->paid_now ?? 0),
        'balance'        => $balance, // إضافة الرصيد هنا أيضاً
        'currency'       => $this->currency,
        'created_at'     => $this->created_at->format('Y/m/d'),
    ];
    }
}