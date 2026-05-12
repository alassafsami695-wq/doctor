<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    protected $fillable = ['id', 'name', 'slug', 'default_price'];

    public function dentalCharts()
    {
        return $this->hasMany(DentalChart::class);
    }
}