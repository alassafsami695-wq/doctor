<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // تغيير الوراثة لتصبح قابلة للتوثيق
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Staff extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'staff'; // التأكد من اسم الجدول

    protected $fillable = [
        'name',
        'email',
        'password',
        'permissions',
        'is_active'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];


    public function subscriptions() {
      
        return $this->hasMany(Subscription::class, 'user_id', 'id')->whereRaw('1 = 0'); 
    }
}