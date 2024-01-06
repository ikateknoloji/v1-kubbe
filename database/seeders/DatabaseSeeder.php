<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\AdminNotification;
use App\Models\Customer;
use App\Models\CustomerInfo;
use App\Models\InvoiceInfo;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderImage;
use App\Models\OrderItem;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\User;
use Database\Factories\OrderManufacturerFactory;
use Database\Factories\OrderOfferFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Faker\Factory as Faker;

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

*/


/*
        Order::factory()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderImage::factory()->count(1))
        ->configureStatus('OC') 
        ->configureInvoiceType('I') 
        ->count(10)
        ->create();

        Order::factory()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(CustomerInfo::factory()->count(1))
        ->has(InvoiceInfo::factory()->count(1))
        ->has(OrderImage::factory()->count(1))
        ->configureStatus('OC') 
        ->configureInvoiceType('C') 
        ->count(10)
        ->create();

*/


/*
        Order::factory()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->configureStatus('OC') 
        ->configureInvoiceType('I') 
        ->count(20)
        ->create();

        Order::factory()
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(InvoiceInfo::factory()->count(1))
        ->configureStatus('OC') 
        ->configureInvoiceType('C') 
        ->count(20)
        ->create();


        Order::factory()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->configureStatus('DP')
        ->configureInvoiceType('I')  
        ->count(20)
        ->create();

        Order::factory()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(InvoiceInfo::factory()->count(1))
        ->configureStatus('DP') 
        ->configureInvoiceType('C') 
        ->count(20)
        ->create();
*/



/*

        
        Order::factory()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->configureStatus('DA') 
        ->configureInvoiceType('I') 
        ->count(20)
        ->create();

        Order::factory()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(InvoiceInfo::factory()->count(1))
        ->configureStatus('DA') 
        ->configureInvoiceType('I') 
        ->count(20)
        ->create();

        Order::factory()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->configureStatus('P') 
        ->configureInvoiceType('I') 
        ->count(20)
        ->create();

        Order::factory()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(InvoiceInfo::factory()->count(1))
        ->configureStatus('P') 
        ->configureInvoiceType('C') 
        ->count(20)
        ->create();
        
*/


/*
        Order::factory()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->configureStatus('PA') 
        ->configureInvoiceType('I') 
        ->count(20)
        ->create();
        
        Order::factory()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(InvoiceInfo::factory()->count(1))
        ->configureStatus('PA') 
        ->configureInvoiceType('C') 
        ->count(20)
        ->create();


        OrderManufacturerFactory::new()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PL')->count(1))
        ->configureStatus('MS')        
        ->configureInvoiceType('I') 
        ->count(20)
        ->create();


        OrderManufacturerFactory::new()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PL')->count(1))
        ->has(InvoiceInfo::factory()->count(1))
        ->configureStatus('MS') 
        ->configureInvoiceType('C') 
        ->count(20)
        ->create();
*/





/*     
 
        OrderManufacturerFactory::new()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PL')->count(1))
        ->configureStatus('PP') 
        ->configureInvoiceType('I') 
        ->count(20)
        ->create();
        
        
        OrderManufacturerFactory::new()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PL')->count(1))
        ->has(InvoiceInfo::factory()->count(1))
        ->configureStatus('PP') 
        ->configureInvoiceType('C') 
        ->count(20)
        ->create();
*/




/*
        OrderManufacturerFactory::new()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PL')->count(1))
        ->has(OrderImage::factory()->configureType('PR')->count(1))
        ->configureStatus('PR') 
        ->configureInvoiceType('I') 
        ->count(20)
        ->create();
        
        OrderManufacturerFactory::new()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PL')->count(1))
        ->has(OrderImage::factory()->configureType('PR')->count(1))
        ->has(InvoiceInfo::factory()->count(1))
        ->configureStatus('PR') 
        ->configureInvoiceType('C') 
        ->count(20)
        ->create();
*/        
        
/*
        OrderManufacturerFactory::new()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PL')->count(1))
        ->has(OrderImage::factory()->configureType('PR')->count(1))
        ->has(OrderImage::factory()->configureType('SC')->count(1))
        ->configureStatus('PIT') 
        ->configureInvoiceType('I') 
        ->count(20)
        ->create();

        OrderManufacturerFactory::new()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PL')->count(1))
        ->has(OrderImage::factory()->configureType('PR')->count(1))
        ->has(OrderImage::factory()->configureType('SC')->count(1))
        ->has(InvoiceInfo::factory()->count(1))
        ->configureStatus('PIT') 
        ->configureInvoiceType('C') 
        ->count(20)
        ->create();   

        OrderManufacturerFactory::new()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PR')->count(1))
        ->has(OrderImage::factory()->configureType('SC')->count(1))
        ->configureStatus('PD') 
        ->configureInvoiceType('I') 
        ->count(20)
        ->create();

        OrderManufacturerFactory::new()
        ->has(CustomerInfo::factory()->count(1))
        ->has(OrderItem::factory()->count(3))
        ->has(OrderAddress::factory()->count(1))
        ->has(OrderImage::factory()->configureType('L')->count(1))
        ->has(OrderImage::factory()->configureType('D')->count(1))
        ->has(OrderImage::factory()->configureType('P')->count(1))
        ->has(OrderImage::factory()->configureType('PR')->count(1))
        ->has(OrderImage::factory()->configureType('SC')->count(1))
        ->has(InvoiceInfo::factory()->count(1))
        ->configureStatus('PD') 
        ->configureInvoiceType('C') 
        ->count(20)
        ->create();
        
        AdminNotification::factory()->count(10)->create();

*/

/*
$faker = Faker::create();

$imagePath = 'public/images/image.png'; // Buradaki dosya yolu ve adını güncelleyin

$product_categories = [
        [
            'category' => 'Mikrofon Süngeri',
            'types' => ['Silindir', 'Üçgen', 'Kare', 'Mini', 'Eski silindir', 'Damla'],
            'image_url' => asset(Storage::url($imagePath)),
            'path' => $faker->word,
        ],
        [
            'category' => 'Kamera Süngeri',
            'types' => ['11 cm', '14 cm', '16 cm', '18 cm', '21 cm'],
            'image_url' => asset(Storage::url($imagePath)),
            'path' => $faker->word,
        ],
        [
            'category' => 'Yaka Mikrofonu',
            'types' => ['3 cm', '5 cm'],
            'image_url' => asset(Storage::url($imagePath)),
            'path' => $faker->word,
        ],
    ];

    foreach ($product_categories as $product_category) {
        $product_category_id = DB::table('product_categories')->insertGetId([
            'category' => $product_category['category'],
            'image_url' => $product_category['image_url'],
            'path' => $product_category['path']
        ]);

        foreach ($product_category['types'] as $product_type) {
            DB::table('product_types')->insert([
                'product_type' => $product_type,
                'product_category_id' => $product_category_id,
                'image_url' => asset(Storage::url($imagePath)),
                'path' => $faker->word,
            ]);
        }
    }
*/

/*
        ProductCategory::factory()->has(ProductType::factory()->count(3))
        ->count(10)
        ->create()
        ;
*/
           // $this->call(UserNotificationsTableSeeder::class);

    }
}
