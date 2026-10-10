<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource. (GET /api/categories)
     */
    public function index(Request $request)
    {
        $query = Category::query();

        // Fitur pencarian berdasarkan nama kategori
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sertakan jumlah produk di tiap kategori (opsional untuk statistik)
        $categories = $query->withCount('products')->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage. (POST /api/categories)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        // Buat slug otomatis dari nama (contoh: "Makanan Ringan" -> "makanan-ringan")
        $validated['slug'] = Str::slug($request->name);

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan',
            'data'    => $category
        ], 201);
    }

    /**
     * Display the specified resource. (GET /api/categories/{category})
     */
    public function show(Category $category)
    {
        // Route Model Binding otomatis mencari kategori berdasarkan ID
        return response()->json([
            'success' => true,
            'data'    => $category->load('products')
        ]);
    }

    /**
     * Update the specified resource in storage. (PUT/PATCH /api/categories/{category})
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        // Update slug jika nama berubah
        $validated['slug'] = Str::slug($request->name);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui',
            'data'    => $category
        ]);
    }

    /**
     * Remove the specified resource from storage. (DELETE /api/categories/{category})
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus'
        ]);
    }
}
