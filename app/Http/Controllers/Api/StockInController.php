<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockInController extends Controller
{
    public function index(Request $request)
    {
        $query = StockIn::with('details.product'); // Atau StockMutation::with('product')

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // UBAH paginate(10) MENJADI get() ATAU HILANGKAN LIMIT
        $data = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer', // Boleh negatif untuk stok out
            'items.*.cost_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $totalCost = 0;
            $firstQty = $request->items[0]['qty'] ?? 0;

            // Kode Awalan Referensi (IN untuk Restock, OUT untuk Pengurangan)
            $prefix = $firstQty < 0 ? 'OUT-' : 'IN-';
            $refNumber = $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $stockIn = StockIn::create([
                'reference_number' => $refNumber,
                'total_cost'       => 0,
                'user_id'          => auth()->id() ?? 1,
                'notes'            => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $qty = (int) $item['qty'];
                $costPrice = (float) ($item['cost_price'] ?? 0);
                $subtotal = $qty * $costPrice;
                $totalCost += $subtotal;

                StockInDetail::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id'  => $item['product_id'],
                    'qty'         => $qty,
                    'cost_price'  => $costPrice,
                    'subtotal'    => $subtotal,
                ]);

                // Update stok produk (Menambah jika +, Mengurangi jika -)
                $product = Product::findOrFail($item['product_id']);
                $product->stock += $qty;

                // Jika Restock bertambah, perbarui harga beli modal
                if ($qty > 0 && $costPrice > 0) {
                    $product->cost_price = $costPrice;
                }

                $product->save();
            }

            $stockIn->update(['total_cost' => $totalCost]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi stok berhasil diproses',
                'data'    => $stockIn->load('details.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses transaksi stok: ' . $e->getMessage()
            ], 500);
        }
    }
}
