<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Manufacturer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::all()->random()->user_id,
            'order_code' => $this->faker->unique()->word,
            'status' => 'OC',
            'manufacturer_id' => null,
            'offer_price' => $this->faker->randomFloat(2, 10, 1000),
            'invoice_type' => $this->faker->randomElement(['I', 'C']),
            'is_rejected' => 'A',
            'note' => $this->faker->optional()->text,
        ];
    }
}
