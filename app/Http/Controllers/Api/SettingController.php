<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::first() ?? Setting::create([
            'store_name' => 'POS Store',
            'receipt_footer_text' => 'Terima Kasih Atas Kunjungan Anda!'
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $setting
        ]);
    }

    /**
     * Update identitas toko.
     */
    public function update(Request $request)
    {
        $setting = Setting::first() ?? new Setting();

        // 1. Jika ada instruksi untuk menghapus logo
        if ($request->boolean('remove_logo')) {
            if ($setting->store_logo && Storage::disk('public')->exists($setting->store_logo)) {
                Storage::disk('public')->delete($setting->store_logo);
            }
            $setting->store_logo = null;
        }

        // 2. Jika ada upload logo baru
        if ($request->hasFile('store_logo')) {
            // Hapus logo lama jika ada
            if ($setting->store_logo && Storage::disk('public')->exists($setting->store_logo)) {
                Storage::disk('public')->delete($setting->store_logo);
            }
            $path = $request->file('store_logo')->store('settings', 'public');
            $setting->store_logo = $path;
        }

        // 3. Simpan data fields lainnya
        $setting->store_name = $request->store_name;
        $setting->store_tagline = $request->store_tagline;
        $setting->store_address = $request->store_address;
        $setting->store_phone = $request->store_phone;
        $setting->store_email = $request->store_email;
        $setting->receipt_footer_text = $request->receipt_footer_text;
        $setting->save();

        return response()->json([
            'status' => 'success',
            'data' => $setting
        ]);
    }
}
