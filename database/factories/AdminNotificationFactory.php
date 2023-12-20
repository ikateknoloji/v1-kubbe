<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminNotification>
 */
class AdminNotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orders = Order::all();

        return [
            'message' => json_encode([
                'title' => "Sipariş Oluşturuldu.",
                'body' => "Yeni Bir siparişin var.",
                'order' => $this->faker->randomElement($orders)->toArray()
            ])   
        ];
    }
}
