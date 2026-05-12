<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    // الحقول التي سيتم تسجيلها عند كل حركة في النظام
    protected $fillable = [
        'user_id',      // من قام بالفعل
        'action',       // نوع الفعل (إضافة، تعديل، حذف)
        'model_type',   // الجدول المتأثر (مريض، فاتورة، موعد)
        'model_id',     // رقم السجل المتأثر
        'description',  // تفاصيل نصية (مثلاً: تم تغيير موعد المريض من السبت للأحد)
        'ip_address'    // عنوان الـ IP لزيادة الأمان
    ];

    /**
     * علاقة السجل بالمستخدم الذي قام بالنشاط
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}