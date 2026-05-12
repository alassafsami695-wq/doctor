<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
   
    protected $fillable = [
        'patient_id', 'total_amount', 'paid_amount', 
        'discount', 'remaining_amount', 'status', 'currency' // إضافة العملة هنا
    ];

    // داخل ملف Invoice.php

    public function updateBalances()
    {
        // الرصيد = المدفوع - المطلوب
        $this->remaining_amount = (float)$this->paid_amount - (float)$this->total_amount;

        // الحالة: إذا كان الرصيد 0 أو أكثر (دفع زيادة) فهو "مدفوع"
        if ($this->remaining_amount >= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
        } else {
            $this->status = 'unpaid';
        }

        return $this->save();
    }
    
    public function patient(): BelongsTo {
        return $this->belongsTo(Patient::class);
    }

    public function payments(): HasMany {
        return $this->hasMany(Payment::class);
    }
}