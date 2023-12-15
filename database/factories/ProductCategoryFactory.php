<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $imagePath = 'public/images/image.png'; // Buradaki dosya yolu ve adını güncelleyin
        return [
            'category' =>  $this->faker->unique()->word,
            'image_url' => asset(Storage::url($imagePath)),
            'path' => $imagePath,
        ];
    }
}
