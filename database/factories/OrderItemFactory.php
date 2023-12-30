<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = OrderItem::class;

    public function definition(): array
    {
        // 'product_type_id' ve 'product_type' alanlarından yalnızca biri dolu olacak
        if (rand(0, 1) === 0) {
            $productTypeId = ProductType::all()->random()->id;
            $productType = null;
        } else {
            $productTypeId = null;
            $productType = $this->faker->word;
        }

        return [
            'order_id' => Order::factory(),
            'product_type_id' => $productTypeId,
            'quantity' => $this->faker->randomNumber(2),
            'color' => $this->faker->colorName,
            'unit_price' => $this->faker->randomFloat(2, 0, 1000),
            'product_type' => $productType,
        ];
    }
}
