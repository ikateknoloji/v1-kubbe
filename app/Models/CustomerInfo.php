<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerInfo extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'surname', 'phone', 'email', 'order_id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
