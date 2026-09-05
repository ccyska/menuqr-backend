<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Warung Mama',
            'slug' => 'warung-mama',
            'logo' => null,
            'description' => 'Warung makanan sederhana dengan berbagai menu favorit.',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '081234567890',
            'whatsapp' => '6281234567890',
            'is_active' => true,
        ]);

        $makanan = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Makanan',
            'slug' => 'makanan',
            'description' => 'Berbagai menu makanan.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $minuman = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Minuman',
            'slug' => 'minuman',
            'description' => 'Berbagai pilihan minuman.',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Menu::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $makanan->id,
            'name' => 'Mie Ayam',
            'slug' => 'mie-ayam',
            'description' => 'Mie ayam dengan topping ayam gurih.',
            'price' => 10000,
            'image' => null,
            'is_available' => true,
            'sort_order' => 1,
        ]);

        Menu::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $makanan->id,
            'name' => 'Nasi Goreng',
            'slug' => 'nasi-goreng',
            'description' => 'Nasi goreng dengan bumbu khas.',
            'price' => 15000,
            'image' => null,
            'is_available' => true,
            'sort_order' => 2,
        ]);

        Menu::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $makanan->id,
            'name' => 'Mie Pangsit',
            'slug' => 'mie-pangsit',
            'description' => 'Mie dengan pangsit dan topping ayam.',
            'price' => 12000,
            'image' => null,
            'is_available' => true,
            'sort_order' => 3,
        ]);

        Menu::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $minuman->id,
            'name' => 'Es Teh',
            'slug' => 'es-teh',
            'description' => 'Es teh manis yang menyegarkan.',
            'price' => 5000,
            'image' => null,
            'is_available' => true,
            'sort_order' => 1,
        ]);

        Menu::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $minuman->id,
            'name' => 'Es Jeruk',
            'slug' => 'es-jeruk',
            'description' => 'Es jeruk segar.',
            'price' => 6000,
            'image' => null,
            'is_available' => true,
            'sort_order' => 2,
        ]);
    }
}