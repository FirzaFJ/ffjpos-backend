<?php

// warung makan

// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use App\Models\Category;
// use App\Models\Product;
// use App\Models\StockIn;
// use App\Models\StockInDetail;
// use App\Models\Transaction;
// use App\Models\TransactionDetail;
// use App\Models\User;
// use Carbon\Carbon;
// use Illuminate\Support\Str;

// class DummyDataSeeder extends Seeder
// {
//     public function run(): void
//     {
//         // 1. Ambil User Kasir/Admin
//         $user = User::first() ?? User::factory()->create([
//             'name'  => 'Admin Utama',
//             'email' => 'admin@pos.com',
//             'role'  => 'admin',
//             'password' => bcrypt('admin123'),
//         ]);

//         // 2. SEED CATEGORIES
//         $categoriesData = [
//             ['name' => 'Makanan Utama', 'slug' => 'makanan-utama'],
//             ['name' => 'Minuman', 'slug' => 'minuman'],
//             ['name' => 'Cemilan & Snack', 'slug' => 'cemilan-snack'],
//             ['name' => 'Paket Hemat', 'slug' => 'paket-hemat'],
//         ];

//         $categories = [];
//         foreach ($categoriesData as $cat) {
//             $categories[] = Category::firstOrCreate(['slug' => $cat['slug']], $cat);
//         }

//         // 3. SEED PRODUCTS (Stok disesuaikan agar cukup untuk 200 transaksi)
//         $productsData = [
//             // Makanan
//             ['category_id' => $categories[0]->id, 'sku' => 'PRD-MKN-001', 'name' => 'Nasi Goreng Spesial', 'price' => 25000, 'cost_price' => 15000, 'stock' => 500],
//             ['category_id' => $categories[0]->id, 'sku' => 'PRD-MKN-002', 'name' => 'Mie Goreng Seafood', 'price' => 28000, 'cost_price' => 18000, 'stock' => 450],
//             ['category_id' => $categories[0]->id, 'sku' => 'PRD-MKN-003', 'name' => 'Nasi Ayam Bakar', 'price' => 22000, 'cost_price' => 14000, 'stock' => 400],
//             ['category_id' => $categories[0]->id, 'sku' => 'PRD-MKN-004', 'name' => 'Kwetiau Sapi', 'price' => 30000, 'cost_price' => 20000, 'stock' => 350],

//             // Minuman
//             ['category_id' => $categories[1]->id, 'sku' => 'PRD-MNM-001', 'name' => 'Es Teh Manis', 'price' => 5000, 'cost_price' => 2000, 'stock' => 800],
//             ['category_id' => $categories[1]->id, 'sku' => 'PRD-MNM-002', 'name' => 'Kopi Latte', 'price' => 18000, 'cost_price' => 9000, 'stock' => 600],
//             ['category_id' => $categories[1]->id, 'sku' => 'PRD-MNM-003', 'name' => 'Jus Alpukat', 'price' => 15000, 'cost_price' => 8000, 'stock' => 500],
//             ['category_id' => $categories[1]->id, 'sku' => 'PRD-MNM-004', 'name' => 'Es Jeruk Segar', 'price' => 7000, 'cost_price' => 3000, 'stock' => 700],

//             // Cemilan
//             ['category_id' => $categories[2]->id, 'sku' => 'PRD-CML-001', 'name' => 'Kentang Goreng', 'price' => 15000, 'cost_price' => 8000, 'stock' => 450],
//             ['category_id' => $categories[2]->id, 'sku' => 'PRD-CML-002', 'name' => 'Roti Bakar Cokelat', 'price' => 16000, 'cost_price' => 8500, 'stock' => 400],
//         ];

//         $products = [];
//         foreach ($productsData as $prod) {
//             $products[] = Product::firstOrCreate(['sku' => $prod['sku']], $prod);
//         }

//         // 4. SEED STOCK IN (Mencatat Restock Awal 1 Tahun Lalu)
//         $stockInDate = Carbon::now()->subDays(370)->setTime(8, 0); // 370 hari lalu

//         $totalCostAllProducts = 0;
//         $stockInDetailsData = [];

//         foreach ($products as $prod) {
//             $subtotalCost = $prod->stock * $prod->cost_price;
//             $totalCostAllProducts += $subtotalCost;

//             $stockInDetailsData[] = [
//                 'product_id' => $prod->id,
//                 'qty'        => $prod->stock, // Qty disamakan dengan stok produk
//                 'cost_price' => $prod->cost_price,
//                 'subtotal'   => $subtotalCost,
//             ];
//         }

//         $stockIn = StockIn::create([
//             'reference_number' => 'STK-' . strtoupper(Str::random(6)),
//             'total_cost'       => $totalCostAllProducts,
//             'user_id'          => $user->id,
//             'notes'            => 'Restock awal persediaan produk (1 tahun)',
//             'created_at'       => $stockInDate,
//             'updated_at'       => $stockInDate,
//         ]);

//         foreach ($stockInDetailsData as $detail) {
//             StockInDetail::create(array_merge($detail, [
//                 'stock_in_id' => $stockIn->id,
//                 'created_at'  => $stockInDate,
//                 'updated_at'  => $stockInDate,
//             ]));
//         }

//         // 5. SEED 200 TRANSAKSI (Terdistribusi 1 Tahun Kebelakang / 365 Hari)
//         $paymentMethods = ['cash', 'qris', 'transfer'];

//         for ($i = 1; $i <= 200; $i++) {
//             // Rentang acak antara 0 hingga 365 hari ke belakang
//             $randomDays = rand(0, 365);
//             $randomHours = rand(8, 21); // Jam 08.00 - 21.00
//             $randomMinutes = rand(0, 59);
//             $transactionDate = Carbon::now()->subDays($randomDays)->setTime($randomHours, $randomMinutes);

//             // Ambil 1 sampai 4 produk acak untuk tiap transaksi
//             $selectedProducts = collect($products)->random(rand(1, 4));

//             $totalAmount = 0;
//             $itemsToInsert = [];

//             foreach ($selectedProducts as $product) {
//                 $qty = rand(1, 3);
//                 $subtotal = $product->price * $qty;
//                 $totalAmount += $subtotal;

//                 $itemsToInsert[] = [
//                     'product_id' => $product->id,
//                     'quantity'   => $qty,
//                     'price'      => $product->price,
//                     'cost_price' => $product->cost_price,
//                     'subtotal'   => $subtotal,
//                 ];

//                 // Kurangi stok barang sesuai penjualan
//                 $product->decrement('stock', $qty);
//             }

//             $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

//             if ($paymentMethod === 'cash') {
//                 $paidAmount = ceil($totalAmount / 10000) * 10000;
//                 if ($paidAmount < $totalAmount) $paidAmount = $totalAmount;
//             } else {
//                 $paidAmount = $totalAmount;
//             }

//             $changeAmount = $paidAmount - $totalAmount;

//             $transaction = Transaction::create([
//                 'user_id'        => $user->id,
//                 'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
//                 'total_amount'   => $totalAmount,
//                 'status'         => 'completed',
//                 'paid_amount'    => $paidAmount,
//                 'change_amount'  => $changeAmount,
//                 'payment_method' => $paymentMethod,
//                 'created_at'     => $transactionDate,
//                 'updated_at'     => $transactionDate,
//             ]);

//             foreach ($itemsToInsert as $item) {
//                 TransactionDetail::create(array_merge($item, [
//                     'transaction_id' => $transaction->id,
//                     'created_at'     => $transactionDate,
//                     'updated_at'     => $transactionDate,
//                 ]));
//             }
//         }

//         $this->command->info('Sukses! 200 data transaksi 1 tahun ke belakang beserta StockIn otomatis berhasil dibuat.');
//     }
// }


// Minimarket / Toko Kelontong / Warung Sembako 40.000 transaksi

// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use App\Models\Category;
// use App\Models\Product;
// use App\Models\StockIn;
// use App\Models\StockInDetail;
// use App\Models\Transaction;
// use App\Models\TransactionDetail;
// use App\Models\User;
// use Carbon\Carbon;
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\DB;

// class DummyDataSeeder extends Seeder
// {
//     public function run(): void
//     {
//         // 1. Ambil atau buat User Kasir/Admin
//         $user = User::first() ?? User::factory()->create([
//             'name'     => 'FFJ',
//             'email'    => 'ffj@pos.com',
//             'role'     => 'admin',
//             'password' => bcrypt('ffj123'),
//         ]);

//         $user = User::factory()->create([
//             'name' => 'Budi',
//             'email' => 'budi@pos.com',
//             'role' => 'cashier',
//             'password' => bcrypt('budid123'),
//         ]);

//         // 2. SEED KATEGORI MINIMARKET
//         $categoriesData = [
//             ['name' => 'Sembako & Kebutuhan Dapur', 'slug' => 'sembako-dapur'],
//             ['name' => 'Minuman Kemasan',          'slug' => 'minuman-kemasan'],
//             ['name' => 'Makanan Ringan & Biskuit',  'slug' => 'snack-biskuit'],
//             ['name' => 'Perawatan Diri & Mandi',    'slug' => 'perawatan-diri'],
//             ['name' => 'Perlengkapan Rumah Tangga', 'slug' => 'rumah-tangga'],
//         ];

//         $categories = [];
//         foreach ($categoriesData as $cat) {
//             $categories[] = Category::firstOrCreate(['slug' => $cat['slug']], $cat);
//         }

//         // 3. SEED PRODUK MINIMARKET (Stok dinaikkan untuk mencukupi 40.000 transaksi)
//         $productsData = [
//             // Sembako & Kebutuhan Dapur
//             ['category_id' => $categories[0]->id, 'sku' => 'MM-SMB-001', 'name' => 'Minyak Goreng Bimoli 2L', 'price' => 38000, 'cost_price' => 33000, 'stock' => 50000],
//             ['category_id' => $categories[0]->id, 'sku' => 'MM-SMB-002', 'name' => 'Beras Pandan Wangi 5kg', 'price' => 78000, 'cost_price' => 68000, 'stock' => 40000],
//             ['category_id' => $categories[0]->id, 'sku' => 'MM-SMB-003', 'name' => 'Gula Pasir Gulaku 1kg',  'price' => 17500, 'cost_price' => 15000, 'stock' => 60000],
//             ['category_id' => $categories[0]->id, 'sku' => 'MM-SMB-004', 'name' => 'Indomie Goreng Spesial', 'price' => 3100,  'cost_price' => 2600,  'stock' => 150000],
//             ['category_id' => $categories[0]->id, 'sku' => 'MM-SMB-005', 'name' => 'Telur Ayam Lapis 1kg',    'price' => 29000, 'cost_price' => 25000, 'stock' => 45000],

//             // Minuman Kemasan
//             ['category_id' => $categories[1]->id, 'sku' => 'MM-MNM-001', 'name' => 'Aqua Botol 600ml',       'price' => 3500,  'cost_price' => 2200,  'stock' => 200000],
//             ['category_id' => $categories[1]->id, 'sku' => 'MM-MNM-002', 'name' => 'Teh Pucuk Harum 350ml',  'price' => 4000,  'cost_price' => 2800,  'stock' => 150000],
//             ['category_id' => $categories[1]->id, 'sku' => 'MM-MNM-003', 'name' => 'Pocari Sweat Botol 500ml','price' => 8500,  'cost_price' => 6500,  'stock' => 80000],
//             ['category_id' => $categories[1]->id, 'sku' => 'MM-MNM-004', 'name' => 'Ultra Milk Cokelat 250ml','price' => 7000,  'cost_price' => 5200,  'stock' => 100000],
//             ['category_id' => $categories[1]->id, 'sku' => 'MM-MNM-005', 'name' => 'Kopi Kapal Api Mix 10s',  'price' => 14500, 'cost_price' => 12000, 'stock' => 70000],

//             // Makanan Ringan & Biskuit
//             ['category_id' => $categories[2]->id, 'sku' => 'MM-SNK-001', 'name' => 'Chitato Sapi Panggang 68g','price' => 11500, 'cost_price' => 9000,  'stock' => 80000],
//             ['category_id' => $categories[2]->id, 'sku' => 'MM-SNK-002', 'name' => 'Oreo Vanilla 133g',       'price' => 10500, 'cost_price' => 8200,  'stock' => 75000],
//             ['category_id' => $categories[2]->id, 'sku' => 'MM-SNK-003', 'name' => 'Silverqueen Milk Choco 58g','price' => 16500, 'cost_price' => 13000, 'stock' => 60000],
//             ['category_id' => $categories[2]->id, 'sku' => 'MM-SNK-004', 'name' => 'Beng Beng Box 20x20g',   'price' => 32000, 'cost_price' => 26000, 'stock' => 50000],

//             // Perawatan Diri & Mandi
//             ['category_id' => $categories[3]->id, 'sku' => 'MM-PRW-001', 'name' => 'Sabun Lifebuoy Red 110g', 'price' => 5000,  'cost_price' => 3800,  'stock' => 90000],
//             ['category_id' => $categories[3]->id, 'sku' => 'MM-PRW-002', 'name' => 'Shampoo Clear Menthol 160ml','price' => 24500, 'cost_price' => 19500, 'stock' => 50000],
//             ['category_id' => $categories[3]->id, 'sku' => 'MM-PRW-003', 'name' => 'Pepsodent Herbal 190g',  'price' => 15500, 'cost_price' => 12000, 'stock' => 70000],

//             // Perlengkapan Rumah Tangga
//             ['category_id' => $categories[4]->id, 'sku' => 'MM-RTH-001', 'name' => 'Detergen Rinso Anti Noda 770g','price' => 22000, 'cost_price' => 17500, 'stock' => 60000],
//             ['category_id' => $categories[4]->id, 'sku' => 'MM-RTH-002', 'name' => 'Pencuci Piring Sunlight 650ml','price' => 14000, 'cost_price' => 11000, 'stock' => 80000],
//             ['category_id' => $categories[4]->id, 'sku' => 'MM-RTH-003', 'name' => 'Tissue Paseo 250 Sheets', 'price' => 18500, 'cost_price' => 14000, 'stock' => 60000],
//         ];

//         $products = [];
//         foreach ($productsData as $prod) {
//             $products[] = Product::firstOrCreate(['sku' => $prod['sku']], $prod);
//         }

//         // 4. SEED STOCK IN (Restock Awal Persediaan 3 Tahun Lalu)
//         $stockInDate = Carbon::now()->subDays(1100)->setTime(8, 0);

//         $totalCostAllProducts = 0;
//         $stockInDetailsData = [];

//         foreach ($products as $prod) {
//             $subtotalCost = $prod->stock * $prod->cost_price;
//             $totalCostAllProducts += $subtotalCost;

//             $stockInDetailsData[] = [
//                 'product_id' => $prod->id,
//                 'qty'        => $prod->stock,
//                 'cost_price' => $prod->cost_price,
//                 'subtotal'   => $subtotalCost,
//             ];
//         }

//         $stockIn = StockIn::create([
//             'reference_number' => 'STK-' . strtoupper(Str::random(6)),
//             'total_cost'       => $totalCostAllProducts,
//             'user_id'          => $user->id,
//             'notes'            => 'Restock awal persediaan barang minimarket (3 tahun)',
//             'created_at'       => $stockInDate,
//             'updated_at'       => $stockInDate,
//         ]);

//         foreach ($stockInDetailsData as $detail) {
//             StockInDetail::create(array_merge($detail, [
//                 'stock_in_id' => $stockIn->id,
//                 'created_at'  => $stockInDate,
//                 'updated_at'  => $stockInDate,
//             ]));
//         }

//         // 5. SEED 40.000 TRANSAKSI (3 Tahun Kebelakang / 1.095 Hari)
//         $paymentMethods = ['cash', 'qris', 'transfer'];
//         $chunkSize = 500; // Proses insert per 500 data agar memori stabil
//         $totalTransactions = 40000;

//         $this->command->info("Memulai pembuatan {$totalTransactions} data transaksi selama 3 tahun ke belakang...");

//         for ($i = 1; $i <= $totalTransactions; $i += $chunkSize) {
//             $transactionsBatch = [];
//             $allDetailsBatch = [];

//             $limit = min($i + $chunkSize - 1, $totalTransactions);

//             for ($j = $i; $j <= $limit; $j++) {
//                 // Rentang acak antara 0 hingga 1095 hari (3 tahun) ke belakang
//                 $randomDays = rand(0, 1095);
//                 $randomHours = rand(7, 22); // Jam operasional (07.00 - 22.00)
//                 $randomMinutes = rand(0, 59);
//                 $transactionDate = Carbon::now()->subDays($randomDays)->setTime($randomHours, $randomMinutes);

//                 // Beli 2 - 6 barang per transaksi secara acak
//                 $selectedProducts = collect($products)->random(rand(2, 6));

//                 $totalAmount = 0;
//                 $currentTransactionDetails = [];

//                 foreach ($selectedProducts as $product) {
//                     $qty = rand(1, 5);
//                     $subtotal = $product->price * $qty;
//                     $totalAmount += $subtotal;

//                     $currentTransactionDetails[] = [
//                         'product_id' => $product->id,
//                         'quantity'   => $qty,
//                         'price'      => $product->price,
//                         'cost_price' => $product->cost_price,
//                         'subtotal'   => $subtotal,
//                         'created_at' => $transactionDate,
//                         'updated_at' => $transactionDate,
//                     ];

//                     // Kurangi stok fisik produk secara langsung
//                     $product->decrement('stock', $qty);
//                 }

//                 $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

//                 if ($paymentMethod === 'cash') {
//                     $paidAmount = ceil($totalAmount / 10000) * 10000;
//                     if ($paidAmount < $totalAmount) $paidAmount = $totalAmount;
//                 } else {
//                     $paidAmount = $totalAmount;
//                 }

//                 $changeAmount = $paidAmount - $totalAmount;
//                 $invoiceNumber = 'INV-' . strtoupper(Str::random(8));

//                 // Simpan transaksi utama agar mendapatkan ID (atau buat batch manual jika ingin super cepat)
//                 $transaction = Transaction::create([
//                     'user_id'        => 2,
//                     'invoice_number' => $invoiceNumber,
//                     'total_amount'   => $totalAmount,
//                     'status'         => 'completed',
//                     'paid_amount'    => $paidAmount,
//                     'change_amount'  => $changeAmount,
//                     'payment_method' => $paymentMethod,
//                     'created_at'     => $transactionDate,
//                     'updated_at'     => $transactionDate,
//                 ]);

//                 foreach ($currentTransactionDetails as $detail) {
//                     $detail['transaction_id'] = $transaction->id;
//                     $allDetailsBatch[] = $detail;
//                 }
//             }

//             // Insert detail transaksi secara massal per chunk
//             if (!empty($allDetailsBatch)) {
//                 TransactionDetail::insert($allDetailsBatch);
//             }

//             $this->command->info("Proses: {$limit} / {$totalTransactions} transaksi berhasil dibuat...");
//         }

//         $this->command->info('Sukses! 40.000 data transaksi 3 tahun ke belakang berhasil dibuat!');
//     }
// }



// / Minimarket / Toko Kelontong / Warung Sembako 5.000 transaksi
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil atau buat User Kasir/Admin
        $user = User::first() ?? User::factory()->create([
            'name'     => 'FFJ',
            'email'    => 'ffj@pos.com',
            'role'     => 'admin',
            'password' => bcrypt('ffj123'),
        ]);

        $cashier = User::firstOrCreate(
            ['email' => 'budi@pos.com'],
            [
                'name'     => 'Budi',
                'role'     => 'cashier',
                'password' => bcrypt('budid123'),
            ]
        );

        // 2. SEED KATEGORI MINIMARKET
        $categoriesData = [
            ['name' => 'Sembako & Kebutuhan Dapur', 'slug' => 'sembako-dapur'],
            ['name' => 'Minuman Kemasan',          'slug' => 'minuman-kemasan'],
            ['name' => 'Makanan Ringan & Biskuit',  'slug' => 'snack-biskuit'],
            ['name' => 'Perawatan Diri & Mandi',    'slug' => 'perawatan-diri'],
            ['name' => 'Perlengkapan Rumah Tangga', 'slug' => 'rumah-tangga'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[] = Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 3. SEED PRODUK MINIMARKET
        $productsData = [
            // Sembako & Kebutuhan Dapur
            ['category_id' => $categories[0]->id, 'sku' => 'MM-SMB-001', 'name' => 'Minyak Goreng Bimoli 2L', 'price' => 38000, 'cost_price' => 33000, 'stock' => 10000],
            ['category_id' => $categories[0]->id, 'sku' => 'MM-SMB-002', 'name' => 'Beras Pandan Wangi 5kg', 'price' => 78000, 'cost_price' => 68000, 'stock' => 8000],
            ['category_id' => $categories[0]->id, 'sku' => 'MM-SMB-003', 'name' => 'Gula Pasir Gulaku 1kg',  'price' => 17500, 'cost_price' => 15000, 'stock' => 10000],
            ['category_id' => $categories[0]->id, 'sku' => 'MM-SMB-004', 'name' => 'Indomie Goreng Spesial', 'price' => 3100,  'cost_price' => 2600,  'stock' => 25000],
            ['category_id' => $categories[0]->id, 'sku' => 'MM-SMB-005', 'name' => 'Telur Ayam Lapis 1kg',    'price' => 29000, 'cost_price' => 25000, 'stock' => 8000],

            // Minuman Kemasan
            ['category_id' => $categories[1]->id, 'sku' => 'MM-MNM-001', 'name' => 'Aqua Botol 600ml',       'price' => 3500,  'cost_price' => 2200,  'stock' => 30000],
            ['category_id' => $categories[1]->id, 'sku' => 'MM-MNM-002', 'name' => 'Teh Pucuk Harum 350ml',  'price' => 4000,  'cost_price' => 2800,  'stock' => 25000],
            ['category_id' => $categories[1]->id, 'sku' => 'MM-MNM-003', 'name' => 'Pocari Sweat Botol 500ml','price' => 8500,  'cost_price' => 6500,  'stock' => 15000],
            ['category_id' => $categories[1]->id, 'sku' => 'MM-MNM-004', 'name' => 'Ultra Milk Cokelat 250ml','price' => 7000,  'cost_price' => 5200,  'stock' => 18000],
            ['category_id' => $categories[1]->id, 'sku' => 'MM-MNM-005', 'name' => 'Kopi Kapal Api Mix 10s',  'price' => 14500, 'cost_price' => 12000, 'stock' => 12000],

            // Makanan Ringan & Biskuit
            ['category_id' => $categories[2]->id, 'sku' => 'MM-SNK-001', 'name' => 'Chitato Sapi Panggang 68g','price' => 11500, 'cost_price' => 9000,  'stock' => 15000],
            ['category_id' => $categories[2]->id, 'sku' => 'MM-SNK-002', 'name' => 'Oreo Vanilla 133g',       'price' => 10500, 'cost_price' => 8200,  'stock' => 15000],
            ['category_id' => $categories[2]->id, 'sku' => 'MM-SNK-003', 'name' => 'Silverqueen Milk Choco 58g','price' => 16500, 'cost_price' => 13000, 'stock' => 10000],
            ['category_id' => $categories[2]->id, 'sku' => 'MM-SNK-004', 'name' => 'Beng Beng Box 20x20g',   'price' => 32000, 'cost_price' => 26000, 'stock' => 8000],

            // Perawatan Diri & Mandi
            ['category_id' => $categories[3]->id, 'sku' => 'MM-PRW-001', 'name' => 'Sabun Lifebuoy Red 110g', 'price' => 5000,  'cost_price' => 3800,  'stock' => 15000],
            ['category_id' => $categories[3]->id, 'sku' => 'MM-PRW-002', 'name' => 'Shampoo Clear Menthol 160ml','price' => 24500, 'cost_price' => 19500, 'stock' => 10000],
            ['category_id' => $categories[3]->id, 'sku' => 'MM-PRW-003', 'name' => 'Pepsodent Herbal 190g',  'price' => 15500, 'cost_price' => 12000, 'stock' => 12000],

            // Perlengkapan Rumah Tangga
            ['category_id' => $categories[4]->id, 'sku' => 'MM-RTH-001', 'name' => 'Detergen Rinso Anti Noda 770g','price' => 22000, 'cost_price' => 17500, 'stock' => 10000],
            ['category_id' => $categories[4]->id, 'sku' => 'MM-RTH-002', 'name' => 'Pencuci Piring Sunlight 650ml','price' => 14000, 'cost_price' => 11000, 'stock' => 12000],
            ['category_id' => $categories[4]->id, 'sku' => 'MM-RTH-003', 'name' => 'Tissue Paseo 250 Sheets', 'price' => 18500, 'cost_price' => 14000, 'stock' => 10000],
        ];

        $products = [];
        foreach ($productsData as $prod) {
            $products[] = Product::firstOrCreate(['sku' => $prod['sku']], $prod);
        }

        // 4. SEED STOCK IN (Restock Awal Persediaan 3 Tahun Lalu)
        $stockInDate = Carbon::now()->subDays(1100)->setTime(8, 0);

        $totalCostAllProducts = 0;
        $stockInDetailsData = [];

        foreach ($products as $prod) {
            $subtotalCost = $prod->stock * $prod->cost_price;
            $totalCostAllProducts += $subtotalCost;

            $stockInDetailsData[] = [
                'product_id' => $prod->id,
                'qty'        => $prod->stock,
                'cost_price' => $prod->cost_price,
                'subtotal'   => $subtotalCost,
            ];
        }

        $stockIn = StockIn::create([
            'reference_number' => 'STK-' . strtoupper(Str::random(6)),
            'total_cost'       => $totalCostAllProducts,
            'user_id'          => $user->id,
            'notes'            => 'Restock awal persediaan barang minimarket (3 tahun)',
            'created_at'       => $stockInDate,
            'updated_at'       => $stockInDate,
        ]);

        foreach ($stockInDetailsData as $detail) {
            StockInDetail::create(array_merge($detail, [
                'stock_in_id' => $stockIn->id,
                'created_at'  => $stockInDate,
                'updated_at'  => $stockInDate,
            ]));
        }

        // 5. SEED 5.000 TRANSAKSI (3 Tahun Kebelakang / 1.095 Hari)
        $paymentMethods = ['cash', 'qris', 'transfer'];
        $chunkSize = 500;
        $totalTransactions = 5000;

        $this->command->info("Memulai pembuatan {$totalTransactions} data transaksi selama 3 tahun ke belakang...");

        for ($i = 1; $i <= $totalTransactions; $i += $chunkSize) {
            $allDetailsBatch = [];
            $limit = min($i + $chunkSize - 1, $totalTransactions);

            for ($j = $i; $j <= $limit; $j++) {
                $randomDays = rand(0, 1095);
                $randomHours = rand(7, 22);
                $randomMinutes = rand(0, 59);
                $transactionDate = Carbon::now()->subDays($randomDays)->setTime($randomHours, $randomMinutes);

                $selectedProducts = collect($products)->random(rand(2, 6));

                $totalAmount = 0;
                $currentTransactionDetails = [];

                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 5);
                    $subtotal = $product->price * $qty;
                    $totalAmount += $subtotal;

                    $currentTransactionDetails[] = [
                        'product_id' => $product->id,
                        'quantity'   => $qty,
                        'price'      => $product->price,
                        'cost_price' => $product->cost_price,
                        'subtotal'   => $subtotal,
                        'created_at' => $transactionDate,
                        'updated_at' => $transactionDate,
                    ];

                    $product->decrement('stock', $qty);
                }

                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

                if ($paymentMethod === 'cash') {
                    $paidAmount = ceil($totalAmount / 10000) * 10000;
                    if ($paidAmount < $totalAmount) $paidAmount = $totalAmount;
                } else {
                    $paidAmount = $totalAmount;
                }

                $changeAmount = $paidAmount - $totalAmount;
                $invoiceNumber = 'INV-' . strtoupper(Str::random(8));

                $transaction = Transaction::create([
                    'user_id'        => $cashier->id,
                    'invoice_number' => $invoiceNumber,
                    'total_amount'   => $totalAmount,
                    'status'         => 'completed',
                    'paid_amount'    => $paidAmount,
                    'change_amount'  => $changeAmount,
                    'payment_method' => $paymentMethod,
                    'created_at'     => $transactionDate,
                    'updated_at'     => $transactionDate,
                ]);

                foreach ($currentTransactionDetails as $detail) {
                    $detail['transaction_id'] = $transaction->id;
                    $allDetailsBatch[] = $detail;
                }
            }

            if (!empty($allDetailsBatch)) {
                TransactionDetail::insert($allDetailsBatch);
            }

            $this->command->info("Proses: {$limit} / {$totalTransactions} transaksi berhasil dibuat...");
        }

        $this->command->info('Sukses! 5.000 data transaksi 3 tahun ke belakang berhasil dibuat!');
    }
}
