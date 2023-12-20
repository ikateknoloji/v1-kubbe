<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public static function getOldestOrdersWithCustomer(array $statuses)
    {
        // Belirli durumları içeren en eski siparişleri al
        $orders = self::whereIn('status', $statuses)
            ->orderBy('updated_at', 'asc')
            ->get();

        // Durum kodlarına göre gruplayarak sadece ilk gelen siparişi döndür
        $groupedOrders = collect();
        
        foreach ($orders as $order) {
            $status = $order->status;
            
            if (!$groupedOrders->has($status)) {
                $groupedOrders[$status] = $order;
                $groupedOrders[$status]->load('customer');
            }
        }

        return $groupedOrders;
    }


    protected $fillable = [
        'customer_id',
        'order_code',
        'status',
        'manufacturer_id',
        'offer_price',
        'invoice_type',
        'is_rejected',
        'note',
        'manufacturer_offer_price'
    ];
    protected $appends = ['original_status'];

     // 'status' sütunu için dönüştürme fonksiyonu
     public function getStatusAttribute($value)
     {
         // 'MA' => 'Üretici Onayı', Kaldırıldı.
         
         $statusMap = [
             'OC' => 'Sipariş Onayı',
             'DP' => 'Tasarım Aşaması',
             'DA' => 'Tasarım Onaylandı',
             'P'  => 'Ödeme Aşaması',
             'PA' => 'Ödeme Alındı',
             'MS' => 'Üretici Seçimi',
             'MO' => 'Üretici Teklifi',
             'OA' => 'Teklifi Onayı',
             'PP' => 'Üretimde',
             'PR' => 'Ürün Hazır',
             'PIT' => 'Ürün Transfer Aşaması',
             'PD' => 'Ürün Teslim Edildi',

         ];

         return $statusMap[$value] ?? $value;
     }
    
    public function getStatusLabelAttribute()
    {
        $statusMap = [
            'OC' => 'Sipariş Onayı',
            'DP' => 'Tasarım Aşaması',
            'DA' => 'Tasarım Onaylandı',
            'P'  => 'Ödeme Aşaması',
            'PA' => 'Ödeme Alındı',
            'MS' => 'Üretici Seçimi',
            'MO' => 'Üretici Teklifi',
            'OA' => 'Teklifi Onayı',
            'PP' => 'Üretimde',
            'PR' => 'Ürün Hazır',
            'PIT' => 'Ürün Transfer Aşaması',
            'PD' => 'Ürün Teslim Edildi',
        ];
    
        return $statusMap[$this->attributes['status']] ?? $this->attributes['status'];
    }

    public function getOriginalStatusAttribute()
    {
        return $this->attributes['status'];
    }
    

    // 'invoice_type' sütunu için dönüştürme fonksiyonu
    public function getInvoiceTypeAttribute($value)
    {
        $invoiceTypeMap = [
            'I' => 'Bireysel',
            'C' => 'Kurumsal',
        ];

        return $invoiceTypeMap[$value] ?? $value;
    }
    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'user_id');
    }
    
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id', 'user_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function orderImages()
    {
        return $this->hasMany(OrderImage::class, 'order_id');
    }

    public function rejects()
    {
        return $this->hasOne(Reject::class);
    }

    public function orderCancellation()
    {
        return $this->hasOne(OrderCancellation::class);
    }

    public function invoiceInfo()
    {
        return $this->hasOne(InvoiceInfo::class, 'order_id');
    }

    
}
