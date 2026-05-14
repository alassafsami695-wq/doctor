<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'starts_at',
        'ends_at',
        'status',
        'months_duration',
        'price',
        'clinic_id'  // أضفه إذا كنت تريد ربط الاشتراك بعيادة محددة
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'price'     => 'decimal:2',
    ];

    /**
     * المستخدم صاحب الاشتراك
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العيادة المرتبطة (اختياري)
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Clinic::class);
    }

    /**
     * الدفعات المرتبطة بالاشتراك
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'subscription_id');
    }

    /**
     * هل الاشتراك فعال حالياً؟
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && Carbon::now()->between($this->starts_at, $this->ends_at);
    }

    /**
     * هل الاشتراك منتهي؟
     */
    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->ends_at);
    }

    /**
     * نطاق: الاشتراكات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('ends_at', '>', Carbon::now());
    }

    /**
     * نطاق: الاشتراكات المنتهية
     */
    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<', Carbon::now())
                     ->orWhere('status', 'expired');
    }
}