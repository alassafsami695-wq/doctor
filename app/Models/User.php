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

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active', 'phone'];

    // لجعل الطبيب ينبهر: إحصائية سريعة لعدد مرضاه
    public function patientsCount() {
        return $this->appointments()->distinct('patient_id')->count();
    }

    public function appointments(): HasMany {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    public function subscriptions(): HasMany {
        return $this->hasMany(Subscription::class);
    }
}
