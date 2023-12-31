<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\ProductCategory;
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
        $productTypeId = $this->faker->randomElement([null, ProductType::pluck('id')->toArray()]);
        $productCategoryId = $productTypeId ? ProductType::find($productTypeId)->product_category_id : null;

        return [
            'order_id' => Order::factory(),
            'product_type_id' => $productTypeId,
            'product_category_id' => $productCategoryId ?? ProductCategory::factory(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'color' => $this->faker->safeColorName,
            'unit_price' => $this->faker->randomFloat(2, 0, 1000),
            'type' => $productTypeId ? null : $this->faker->word,
        ];
    }
}
