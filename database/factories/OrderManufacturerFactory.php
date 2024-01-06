<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Manufacturer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderManufacturerFactory extends Factory
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
            'order_name' => $this->faker->word(),
            'customer_id' => Customer::all()->random()->user_id,
            'order_code' => $this->faker->unique()->randomNumber(5) . $this->faker->word,
            'manufacturer_id' => Manufacturer::all()->random()->user_id,
            'offer_price' => $this->faker->randomFloat(2, 10, 1000),
            'invoice_type' => $this->faker->randomElement(['I', 'C']),
            'is_rejected' => 'A',
            'note' => $this->faker->optional()->text,
            'production_start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'production_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'delivery_date' => $this->faker->dateTimeBetween('now', '+1 month')
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

    public function configureInvoiceType($invoiceType)
    {
        return $this->state(function (array $attributes) use ($invoiceType) {
            return [
                'invoice_type' => $invoiceType,
            ];
        });
    }
}
