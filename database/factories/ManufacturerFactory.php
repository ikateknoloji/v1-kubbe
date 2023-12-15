<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    protected $model = Manufacturer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $imagePath = 'public/images/image.png'; // Buradaki dosya yolu ve adını güncelleyin

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->firstName,
            'surname' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'tax_number' => $this->faker->numerify('##########'),
            'tax_office' => $this->faker->word,
            'company_name' => $this->faker->company,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'district' => $this->faker->word,
            'country' => $this->faker->country,
            'image_url' => asset(Storage::url($imagePath)),
            'path' => $imagePath,
        ];
    }
}
