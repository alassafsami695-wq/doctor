<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'contact', 'phone'];

    public function logs() {
        return $this->hasMany(PartnerLog::class)->orderBy('transaction_date', 'desc');
    }
}
