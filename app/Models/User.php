<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role', 
        'is_active', 
        'phone', 
        'clinic_address', 
        'verification_code'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
    ];

    protected $appends = ['has_active_subscription'];

    // ✅ تحويل 'dentist' إلى 'doctor' للـ Frontend
    public function getRoleAttribute($value)
    {
        return $value === 'dentist' ? 'doctor' : $value;
    }

    // ✅ عند الحفظ، احفظ 'dentist' في قاعدة البيانات
    public function setRoleAttribute($value)
    {
        $this->attributes['role'] = $value === 'doctor' ? 'dentist' : $value;
    }

    public function patientsCount() {
        return $this->appointments()->distinct('patient_id')->count();
    }

    public function appointments(): HasMany {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    public function subscriptions(): HasMany {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    // جلب آخر اشتراك بدقة وبشكل آمن
    public function subscription(): HasOne {
        return $this->hasOne(Subscription::class, 'user_id')->latestOfMany();
    }

    // ✅ تعديل الـ Accessor ليتوافق تماماً مع قيم الـ enum (active أو trial) والتواريخ
    public function getHasActiveSubscriptionAttribute() {
        $sub = $this->subscription;
        if (!$sub) {
            return false;
        }
        
        // يكون فعالاً إذا كانت الحالة active أو trial وتاريخ النهاية لم يأقِ بعد
        return in_array($sub->status, ['active', 'trial']) && $sub->ends_at > now();
    }
}