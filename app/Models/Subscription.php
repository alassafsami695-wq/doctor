<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    protected $fillable = ['user_id', 'starts_at', 'ends_at', 'status', 'months_duration'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    // التحقق هل الاشتراك فعال حالياً
    public function isActive() {
        return $this->status === 'active' && Carbon::now()->between($this->starts_at, $this->ends_at);
    }


    public function payments() {
    // ربط الاشتراك بالدفعات (Invoice id المرتبط بالاشتراك)
    return $this->hasMany(Payment::class, 'subscription_id'); 
    }

    public function clinic() {
        return $this->belongsTo(Clinic::class);
    }
}