<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderImage extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'order_id',
        'type',
        'image_url',
        'path',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
