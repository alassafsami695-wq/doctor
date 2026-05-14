<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // تحديث: إضافة الحقول الجديدة هنا
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

    // ✅ Accessor: تحويل 'dentist' إلى 'doctor' للـ Frontend
    public function getRoleAttribute($value)
    {
        return $value === 'dentist' ? 'doctor' : $value;
    }

    // ✅ Mutator: عند الحفظ، احفظ 'dentist' في قاعدة البيانات
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
        return $this->hasMany(Subscription::class);
    }

    public function subscription() {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    protected $appends = ['has_active_subscription'];

    public function getHasActiveSubscriptionAttribute() {
        return $this->subscription()->where('status', 'active')
                                    ->where('ends_at', '>', now())
                                    ->exists();
    }
}