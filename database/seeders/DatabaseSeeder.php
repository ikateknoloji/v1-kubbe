<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\AdminNotification;
use App\Models\Customer;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderImage;
use App\Models\OrderItem;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\User;
use Database\Factories\OrderManufacturerFactory;
use Database\Factories\OrderOfferFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
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
        */
    
        /*
        Order::factory()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->count(1))
        ->count(20)
        ->create();

        Order::factory()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->configureStatus('OC') 
        ->count(40)
        ->create();

        Order::factory()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->configureStatus('DP') 
        ->count(40)
        ->create();
        */
    /*
        Order::factory()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->configureStatus('DA') 
        ->count(40)
        ->create();

        Order::factory()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->configureStatus('P') 
        ->count(40)
        ->create();
*/
        /*
        Order::factory()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->configureStatus('PA') 
        ->count(40)
        ->create();

        OrderManufacturerFactory::new()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->configureStatus('MS') 
        ->count(40)
        ->create();
        */


        /*
        OrderOfferFactory::new()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->configureStatus('MO') 
        ->count(40)
        ->create();
        
        OrderOfferFactory::new()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->configureStatus('OA') 
        ->count(40)
        ->create();

        OrderOfferFactory::new()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->configureStatus('PP') 
        ->count(40)
        ->create();
        */

        /*
        OrderOfferFactory::new()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PR')->count(1))
        ->configureStatus('PR') 
        ->count(40)
        ->create();
        */
        

        /*
        OrderOfferFactory::new()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PR')->count(1))
        ->has(OrderImage::factory()->configureType('SC')->count(1))
        ->configureStatus('PIT') 
        ->count(40)
        ->create();
        

        OrderOfferFactory::new()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PR')->count(1))
        ->has(OrderImage::factory()->configureType('SC')->count(1))
        ->configureStatus('PD') 
        ->count(40)
        ->create();

        */
        AdminNotification::factory()->count(10)->create();
        }
}
