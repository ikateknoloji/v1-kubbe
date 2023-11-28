<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderImage>
 */
class OrderImageFactory extends Factory
{
    protected $model = OrderImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'type' => 'L',
            'image_url' => $this->faker->imageUrl(),
            'path' => $this->faker->word,
        ];
    }
}
