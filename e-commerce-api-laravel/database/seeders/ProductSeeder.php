<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Wireless Bluetooth Headphones',
                'description' => 'Premium noise-cancelling headphones with 30 hour battery life. Crystal clear sound quality and comfortable design for all day wear.',
                'price' => 79.99,
                'compare_price' => 99.99,
                'stock' => 50,
                'is_active' => true
            ],
            [
                'name' => 'Smart Watch Pro',
                'description' => 'Track your fitness, monitor your health, and stay connected. Water-resistant with 7-day battery life.',
                'price' => 299.99,
                'compare_price' => 399.99,
                'stock' => 30,
                'is_active' => true
            ],
            [
                'name' => 'Laptop Stand Aluminum',
                'description' => 'Ergonomic laptop stand made from premium aluminum. Adjustable height and angle for perfect viewing.',
                'price' => 49.99,
                'stock' => 100,
                'is_active' => true
            ],
            [
                'name' => 'USB-C Hub 7-in-1',
                'description' => 'Expand your connectivity with 7 ports including HDMI, USB 3.0, SD card reader, and more.',
                'price' => 39.99,
                'stock' => 75,
                'is_active' => true
            ],
            [
                'name' => 'Mechanical Keyboard RGB',
                'description' => 'Professional gaming keyboard with customizable RGB lighting and Cherry MX switches.',
                'price' => 129.99,
                'compare_price' => 159.99,
                'stock' => 25,
                'is_active' => true
            ],
            [
                'name' => 'Wireless Mouse Ergonomic',
                'description' => 'Comfortable vertical mouse design reduces wrist strain. Precise optical sensor for accurate tracking.',
                'price' => 34.99,
                'stock' => 60,
                'is_active' => true
            ],
            [
                'name' => 'Portable SSD 1TB',
                'description' => 'Ultra-fast portable storage with USB-C connectivity. Transfer speeds up to 1050MB/s.',
                'price' => 149.99,
                'compare_price' => 199.99,
                'stock' => 40,
                'is_active' => true
            ],
            [
                'name' => 'Webcam 4K Ultra HD',
                'description' => 'Professional quality webcam with autofocus and built-in microphone. Perfect for video calls.',
                'price' => 89.99,
                'stock' => 35,
                'is_active' => true
            ],
            [
                'name' => 'Phone Stand Adjustable',
                'description' => 'Sturdy phone holder for desk. Compatible with all smartphone sizes.',
                'price' => 19.99,
                'stock' => 150,
                'is_active' => true
            ],
            [
                'name' => 'Cable Organizer Set',
                'description' => 'Keep your cables neat and tidy with this 10 piece organizer set.',
                'price' => 12.99,
                'stock' => 200,
                'is_active' => true
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
