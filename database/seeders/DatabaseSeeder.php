<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Akun Kasir Default
        $user = User::create([
            'name' => 'Kasir Utami',
            'email' => 'kasir@pos.com',
            'role' => 'cashier',
            'password' => Hash::make('password123'),
        ]);

        $user = User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@gmail.com',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
        ]);

        // 2. Buat Kategori Sample
        $categoryMakanan = Category::create([
            'name' => 'Makanan',
            'slug' => 'makanan',
        ]);

        $categoryMinuman = Category::create([
            'name' => 'Minuman',
            'slug' => 'minuman',
        ]);

        // 3. Buat Produk Sample
        $products = [
            [
                'category_id' => $categoryMakanan->id,
                'sku' => 'PRD-MKN-001',
                'name' => 'Nasi Goreng Spesial',
                'price' => 25000,
                'cost_price' => 20000,
                'stock' => 0,
            ],
            [
                'category_id' => $categoryMakanan->id,
                'sku' => 'PRD-MKN-002',
                'name' => 'Mie Goreng Seafood',
                'price' => 28000,
                'cost_price' => 22000,
                'stock' => 0,
            ],
            [
                'category_id' => $categoryMinuman->id,
                'sku' => 'PRD-MNM-001',
                'name' => 'Es Teh Manis',
                'price' => 5000,
                'cost_price' => 3000,
                'stock' => 0,
            ],
            [
                'category_id' => $categoryMinuman->id,
                'sku' => 'PRD-MNM-002',
                'name' => 'Kopi Latte',
                'price' => 18000,
                'cost_price' => 12000,
                'stock' => 0,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
