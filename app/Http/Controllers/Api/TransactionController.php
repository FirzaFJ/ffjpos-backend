<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    // Riwayat Transaksi Penjualan
    public function index()
    {
        $transactions = Transaction::with(['user', 'details.product'])
            ->latest()
            ->paginate(1500);

        return response()->json([
            'success' => true,
            'data'    => $transactions
        ]);
    }

    // Proses Checkout Kasir
    public function store(Request $request)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'paid_amount'        => 'required|numeric|min:0',
            'payment_method'     => 'required|string',
        ]);

        return DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $itemsToInsert = [];

            // 1. Validasi Stok & Hitung Total
            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok untuk produk '{$product->name}' tidak mencukupi. Sisa stok: {$product->stock}");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $itemsToInsert[] = [
                    'product'  => $product,
                    'quantity' => $item['quantity'],
                    'price'    => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            if ($request->paid_amount < $totalAmount) {
                return response()->json([
                    'message' => 'Uang pembayaran kurang dari total belanjaan.'
                ], 422);
            }

            // 2. Buat Record Transaksi Utama
            $transaction = Transaction::create([
                'user_id'        => $request->user()->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'total_amount'   => $totalAmount,
                'paid_amount'    => $request->paid_amount,
                'change_amount'  => $request->paid_amount - $totalAmount,
                'payment_method' => $request->payment_method,
            ]);

            // 3. Simpan Detail Transaksi & Kurangi Stok Produk
            foreach ($itemsToInsert as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $item['product']->id,
                    'quantity'       => $item['quantity'],
                    'price'          => $item['price'],
                    'cost_price'     => $item['product']->cost_price ?? 0, // PERBAIKAN: Menggunakan $item['product']
                    'subtotal'       => $item['subtotal'],
                ]);

                // Potong Stok
                $item['product']->decrement('stock', $item['quantity']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses',
                'data'    => $transaction->load('details.product')
            ], 201);
        });
    }

    // Detail Struk / Nota Transaksi
    public function show($id)
    {
        $transaction = Transaction::with(['user', 'details.product'])->find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $transaction
        ]);
    }


    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::with('details')->findOrFail($id);

            if ($transaction->status === 'cancelled') {
                return response()->json(['message' => 'Transaksi sudah dibatalkan sebelumnya.'], 400);
            }

            // 1. Kembalikan stok produk
            foreach ($transaction->details as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->increment('stock', $detail->quantity);
                }
            }

            // 2. Ubah status transaksi
            $transaction->update(['status' => 'cancelled']);

            DB::commit();
            return response()->json(['message' => 'Transaksi berhasil dibatalkan dan stok telah dikembalikan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membatalkan transaksi: ' . $e->getMessage()], 500);
        }
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::with('details')->findOrFail($id);

            if ($transaction->status !== 'cancelled') {
                return response()->json(['message' => 'Transaksi ini tidak dalam status dibatalkan.'], 400);
            }

            // 1. Cek ketersediaan stok & potong kembali stok produk
            foreach ($transaction->details as $detail) {
                $product = Product::lockForUpdate()->find($detail->product_id);
                if (!$product) {
                    throw new \Exception("Produk ID {$detail->product_id} sudah tidak ditemukan.");
                }

                if ($product->stock < $detail->quantity) {
                    throw new \Exception("Gagal memulihkan. Stok produk '{$product->name}' tidak mencukupi (Tersedia: {$product->stock}, Butuh: {$detail->quantity}).");
                }
            }

            // Potong stok setelah semua item terverifikasi aman
            foreach ($transaction->details as $detail) {
                $product = Product::find($detail->product_id);
                $product->decrement('stock', $detail->quantity);
            }

            // 2. Kembalikan status ke completed
            $transaction->update(['status' => 'completed']);

            DB::commit();
            return response()->json(['message' => 'Status transaksi berhasil dipulihkan menjadi Selesai.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // Hapus Transaksi Permanen (Hanya untuk yang berstatus cancelled)
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::with('details')->findOrFail($id);

            // Validasi: Hanya transaksi yang sudah dibatalkan yang boleh dihapus permanen
            if ($transaction->status !== 'cancelled') {
                return response()->json([
                    'message' => 'Hanya transaksi yang sudah dibatalkan yang dapat dihapus permanen.'
                ], 400);
            }

            // Hapus detail transaksi terlebih dahulu
            $transaction->details()->delete();

            // Hapus data transaksi utama
            $transaction->delete();

            DB::commit();
            return response()->json(['message' => 'Transaksi berhasil dihapus secara permanen.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus transaksi: ' . $e->getMessage()], 500);
        }
    }

    // Endpoint Dashboard Analytics
    public function dashboardStats(Request $request)
    {
        $period = $request->query('period', 'daily');

        // 1. Ringkasan Omset & Transaksi Keseluruhan
        $completedTx = Transaction::where('status', 'completed');
        $totalOmset = (float) $completedTx->sum('total_amount');
        $totalTransactions = $completedTx->count();

        // 2. Ringkasan HARI INI vs HARI KEMARIN
        $todayOmset = (float) Transaction::where('status', 'completed')
            ->whereDate('created_at', now()->today())
            ->sum('total_amount');

        $yesterdayOmset = (float) Transaction::where('status', 'completed')
            ->whereDate('created_at', now()->yesterday())
            ->sum('total_amount');

        $todayCount = Transaction::where('status', 'completed')
            ->whereDate('created_at', now()->today())
            ->count();

        // Hitung persentase pertumbuhan omset hari ini dibanding kemarin
        $growthPercentage = 0;
        if ($yesterdayOmset > 0) {
            $growthPercentage = (($todayOmset - $yesterdayOmset) / $yesterdayOmset) * 100;
        } elseif ($todayOmset > 0) {
            $growthPercentage = 100; // Naik 100% jika kemarin 0
        }

        // 3. Produk Stok Menipis (Stok <= 5)
        $lowStockProducts = Product::where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->limit(6)
            ->get(['id', 'name', 'stock', 'price']);

        // 4. Query Grafik Omset berdasarkan Periode
        $salesQuery = Transaction::where('status', 'completed');

        if ($period === 'monthly') {
            $twelveMonthsAgo = now()->subMonths(11)->startOfMonth();
            $dailySales = $salesQuery->where('created_at', '>=', $twelveMonthsAgo)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as date_key, DATE_FORMAT(created_at, '%b %Y') as date, SUM(total_amount) as total, COUNT(id) as count")
                ->groupBy('date_key', 'date')
                ->orderBy('date_key', 'ASC')
                ->get();
        } elseif ($period === 'yearly') {
            $fiveYearsAgo = now()->subYears(4)->startOfYear();
            $dailySales = $salesQuery->where('created_at', '>=', $fiveYearsAgo)
                ->selectRaw("DATE_FORMAT(created_at, '%Y') as date, SUM(total_amount) as total, COUNT(id) as count")
                ->groupBy('date')
                ->orderBy('date', 'ASC')
                ->get();
        } else {
            $sevenDaysAgo = now()->subDays(6)->startOfDay();
            $dailySales = $salesQuery->where('created_at', '>=', $sevenDaysAgo)
                ->selectRaw("DATE(created_at) as date_key, DATE_FORMAT(created_at, '%d %b') as date, SUM(total_amount) as total, COUNT(id) as count")
                ->groupBy('date_key', 'date')
                ->orderBy('date_key', 'ASC')
                ->get();
        }

        $formattedSales = $dailySales->map(function ($item) {
            return [
                'date'  => $item->date,
                'total' => (float) $item->total,
                'count' => (int) $item->count,
            ];
        });

        // 5. Top 5 Produk Terlaris
        $topProducts = TransactionDetail::whereHas('transaction', function ($q) {
                $q->where('status', 'completed');
            })
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderBy('total_qty', 'DESC')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name'  => $item->product ? $item->product->name : 'Produk Dihapus',
                    'qty'   => (int) $item->total_qty,
                    'sales' => (float) $item->total_sales,
                ];
            });

        // 6. Proporsi Metode Pembayaran
        $paymentMethods = Transaction::where('status', 'completed')
            ->selectRaw('UPPER(payment_method) as method, COUNT(id) as count')
            ->groupBy('method')
            ->get()
            ->map(function ($item) {
                return [
                    'name'  => $item->method ?: 'CASH',
                    'value' => (int) $item->count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_omset'        => $totalOmset,
                'total_transactions' => $totalTransactions,
                'today_omset'        => $todayOmset,
                'yesterday_omset'    => $yesterdayOmset,
                'today_count'        => $todayCount,
                'growth_percentage'  => round($growthPercentage, 1),
                'low_stock_products' => $lowStockProducts,
                'sales_chart'        => $formattedSales,
                'top_products'       => $topProducts,
                'payment_methods'    => $paymentMethods,
            ]
        ]);
    }
}
