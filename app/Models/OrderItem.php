<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_type_id',
        'product_category_id',
        'quantity',
        'color',
        'unit_price',
        'type'
    ];
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }
}
