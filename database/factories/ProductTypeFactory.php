<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

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
        $imagePath = 'public/images/image.png'; // Buradaki dosya yolu ve adını güncelleyin

        return [
            'product_type' => $this->faker->unique()->word,
            'product_category_id' => ProductCategory::factory(),
            'image_url' => asset(Storage::url($imagePath)),
            'path' => $this->faker->word,
        ];
    }
}
