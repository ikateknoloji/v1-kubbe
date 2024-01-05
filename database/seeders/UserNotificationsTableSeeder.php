<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Manufacturer;
use App\Models\Order;

class UserNotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manufacturer_user_id = 39; // Örnek bir üretici kullanıcı ID'si

        // Üreticiyi ve ona ait rastgele bir siparişi alın
        $manufacturer = Manufacturer::where('user_id', $manufacturer_user_id)->first();

        if ($manufacturer) {
            $order = Order::where('manufacturer_id', $manufacturer->user_id)->inRandomOrder()->first();

            if ($order) {
                // Sipariş detaylarına dayalı bir bildirim oluşturun
                $notification = [
                    'message' => json_encode([
                        'title' => 'Sipariş Güncellemesi',
                        'body' => "Siparişinizin detayları aşağıdadır:",
                        'order' => [
                            'order_id' => $order->id,
                            'order_name' => $order->order_name,
                            'order_code' => $order->order_code,
                            'status' => $order->status,
                            'offer_price' => $order->offer_price,
                            'invoice_type' => $order->invoice_type,
                            'is_rejected' => $order->is_rejected,
                            'note' => $order->note,
                            'manufacturer' => $manufacturer->name,
                        ],
                    ]),
                    'is_read' => false,
                ];

                // Bildirimi veritabanına ekleyin
                $manufacturer->user->notifications()->create($notification);
            }
        }
    }
}
