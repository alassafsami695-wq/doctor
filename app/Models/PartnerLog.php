<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerLog extends Model
{
    /**
     * الحقول التي يمكن تعبئتها جماعياً (Mass Assignment).
     *
     */
    protected $fillable = [
        'partner_id',
        'type',
        'amount',
        'note',
        'transaction_date'
    ];

    /**
     * تحديد نوع الحقول لضمان التعامل معها بشكل صحيح (Casting).
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * الحصول على الشريك (المخبر أو الشركة) الذي تتبع له هذه العملية.
     *
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}