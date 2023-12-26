<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\InvoiceInfo;
use App\Models\Order;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceInfo>
 */
class InvoiceInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = InvoiceInfo::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'company_namae' => $this->faker->company,
            'address' => $this->faker->address,
            'tax_office' => $this->faker->word,
            'tax_number' => $this->faker->randomNumber(9, true),
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
