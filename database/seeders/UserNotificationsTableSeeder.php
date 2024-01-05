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
                        'order' => $order->toArray(),
                    ]),
                    'is_read' => false,
                ];

                // Bildirimi veritabanına ekleyin
                $manufacturer->user->notifications()->create($notification);
            }
        }
    }
}
