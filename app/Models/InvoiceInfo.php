<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceInfo extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'order_id',
        'company_name',
        'address',
        'tax_office',
        'tax_number',
        'email',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
