<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Customer;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderImage;
use App\Models\OrderItem;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        
        // Admin kullanıcısını oluştur
        User::factory()->create(['user_type' => 'admin']);

        User::factory()
        ->has(Customer::factory()->count(1))
        ->count(20)
        ->create(['user_type' => 'customer']);

        User::factory()
        ->has(Manufacturer::factory()->count(1))
        ->count(20)
        ->create(['user_type' => 'manufacturer']);



        ProductCategory::factory()->has(ProductType::factory()->count(3))
        ->count(10)
        ->create()
        ;
        


    
        Order::factory()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->count(1))
        ->count(10)
        ->create();
    }
}
