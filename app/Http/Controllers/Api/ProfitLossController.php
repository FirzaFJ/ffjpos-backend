<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitLossController extends Controller
{
    public function getReport(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            // Buat Base Query
            $baseQuery = TransactionDetail::query();

            // JIKA ADA FILTER TANGGAL, TERAPKAN WHEREBETWEEN
            if (!empty($startDate) && !empty($endDate)) {
                $startDateTime = $startDate . ' 00:00:00';
                $endDateTime = $endDate . ' 23:59:59';

                $baseQuery->whereHas('transaction', function ($q) use ($startDateTime, $endDateTime) {
                    $q->whereBetween('created_at', [$startDateTime, $endDateTime]);
                });
            }

            // 1. Total Penjualan Kotor (Revenue) = quantity * price
            $totalRevenue = (clone $baseQuery)->sum(DB::raw('COALESCE(quantity, 0) * COALESCE(price, 0)')) ?? 0;

            // 2. Total HPP / Modal (COGS) = quantity * cost_price
            $totalCogs = (clone $baseQuery)->sum(DB::raw('COALESCE(quantity, 0) * COALESCE(cost_price, 0)')) ?? 0;

            // 3. Keuntungan Bersih = Revenue - COGS
            $netProfit = $totalRevenue - $totalCogs;

            // 4. Persentase Margin Keuntungan (%)
            $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 2) : 0;

            // 5. Total Barang Terjual
            $totalItemsSold = (clone $baseQuery)->sum('quantity') ?? 0;

            // 6. Top 5 Produk Berdasarkan Profit
            $topProfitableQuery = TransactionDetail::select(
                    'product_id',
                    DB::raw('SUM(COALESCE(quantity, 0)) as total_qty'),
                    DB::raw('SUM(COALESCE(quantity, 0) * COALESCE(price, 0)) as total_revenue'),
                    DB::raw('SUM(COALESCE(quantity, 0) * COALESCE(cost_price, 0)) as total_cogs'),
                    DB::raw('SUM((COALESCE(quantity, 0) * COALESCE(price, 0)) - (COALESCE(quantity, 0) * COALESCE(cost_price, 0))) as total_profit')
                );

            // Terapkan filter tanggal ke Top Produk jika ada
            if (!empty($startDate) && !empty($endDate)) {
                $topProfitableQuery->whereHas('transaction', function ($q) use ($startDateTime, $endDateTime) {
                    $q->whereBetween('created_at', [$startDateTime, $endDateTime]);
                });
            }

            $topProfitableProducts = $topProfitableQuery->with(['product' => function ($q) {
                    $q->select('id', 'name');
                }])
                ->groupBy('product_id')
                ->orderBy('total_profit', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'period' => [
                        'start_date' => $startDate ?? 'Semua',
                        'end_date' => $endDate ?? 'Semua',
                    ],
                    'summary' => [
                        'total_revenue' => (float) $totalRevenue,
                        'total_cogs' => (float) $totalCogs,
                        'net_profit' => (float) $netProfit,
                        'profit_margin' => (float) $profitMargin,
                        'total_items_sold' => (int) $totalItemsSold,
                    ],
                    'top_products' => $topProfitableProducts
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat laporan: ' . $e->getMessage()
            ], 500);
        }
    }
}
