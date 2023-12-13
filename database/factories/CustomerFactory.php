<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{

    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

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
            'image_url' => "http://127.0.0.1:8000/storage/images/image.png",
            'path' => $this->faker->word,
        ];
    }
}
