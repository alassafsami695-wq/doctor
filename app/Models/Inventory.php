<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = ['item_name', 'category', 'quantity', 'low_stock_threshold', 'expiry_date'];

    protected $casts = [
        'expiry_date' => 'date',
    ];
}