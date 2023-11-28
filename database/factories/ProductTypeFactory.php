<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductType>
 */
class ProductTypeFactory extends Factory
{
    protected $model = ProductType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productCategories = ProductCategory::all();

        return [
            'product_type' => $this->faker->unique()->word,
            'product_category_id' => ProductCategory::factory(),
            'image_url' => $this->faker->imageUrl(),
            'path' => $this->faker->word,
        ];
    }
}
