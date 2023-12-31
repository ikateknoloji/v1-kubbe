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
        $productCategory = ProductCategory::inRandomOrder()->first();
        $productType = $productCategory->productTypes->random();

        $productTypeId = $this->faker->randomElement([$productType->id, null]);
        $productCategoryId = $productTypeId ? $productType->product_category_id : $productCategory->id;

        return [
            'order_id' => Order::factory(),
            'product_type_id' => $productTypeId,
            'product_category_id' => $productCategoryId,
            'quantity' => $this->faker->numberBetween(1, 100),
            'color' => $this->faker->safeColorName,
            'unit_price' => $this->faker->randomFloat(2, 0, 1000),
            'type' => $productTypeId ? null : $this->faker->word,
        ];
    }
}
