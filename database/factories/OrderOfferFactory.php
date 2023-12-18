<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Customer;
use App\Models\Manufacturer;
use App\Models\Order;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderOfferFactory extends Factory
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
            'order_code' => $this->faker->unique()->randomNumber(5) . $this->faker->word,
            'manufacturer_id' => Manufacturer::all()->random()->user_id,
            'offer_price' => $this->faker->randomFloat(2, 10, 1000),
            'invoice_type' => $this->faker->randomElement(['I', 'C']),
            'is_rejected' => 'A',
            'manufacturer_offer_price' => $this->faker->randomFloat(2, 1000, 4000),
            'note' => $this->faker->optional()->text,
        ];
    }

    public function configureStatus($status)
    {
        return $this->state(function (array $attributes) use ($status) {
            return [
                'status' => $status,
            ];
        });
    }
}
