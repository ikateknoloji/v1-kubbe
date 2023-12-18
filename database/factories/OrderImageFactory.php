<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

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
        $isImage = $this->faker->boolean; // Rastgele bir boolean değer oluşturur
        $filePath = $isImage ? 'public/images/image.png' : 'public/images/bill.pdf'; // Dosya türüne göre dosya yolu seçer
        $mimeType = $isImage ? 'image/png' : 'application/pdf'; // Dosya türüne göre MIME türünü seçer

        return [
            'order_id' => Order::factory(),
            'image_url' => asset(Storage::url($filePath)),
            'path' => $filePath,
            'mime_type' => $mimeType, // Yeni eklenen sütun
        ];
    }
    public function configureType($type)
    {
        return $this->state(function (array $attributes) use ($type) {
            return [
                'type' => $type,
            ];
        });
    }
}
