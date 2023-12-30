<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;
    protected $fillable = ['category', 'image_url', 'path'];
    
    public function productTypes()
    {
        return $this->hasMany(ProductType::class, 'product_category_id');
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
