<?php

namespace App\Http\Controllers;

use App\Models\Dayahisap;
use Illuminate\Http\Request;

class DayahisapController extends Controller
{
    /**
     * Menyimpan atau mengupdate data daya hisap ke database
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'power_mode' => 'required|in:eco,normal,strong',
                'value' => 'required|integer|between:150,255'
            ]);

            // Cari atau buat record pertama (hanya satu record yang ada)
            $dayahisap = Dayahisap::first();

            if ($dayahisap) {
                // Jika sudah ada, update value-nya
                $dayahisap->update([
                    'value' => $validated['value']
                ]);
                $message = 'Data daya hisap berhasil diupdate';
            } else {
                // Jika belum ada, buat record baru
                $dayahisap = Dayahisap::create([
                    'value' => $validated['value']
                ]);
                $message = 'Data daya hisap berhasil disimpan';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $dayahisap
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil data daya hisap
     */
    public function show()
    {
        $dayahisap = Dayahisap::first();
        
        if (!$dayahisap) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $dayahisap
        ], 200);
    }

    /**
     * Mendapatkan value terbaru
     */
    public function latest()
    {
        $dayahisap = Dayahisap::first();
        
        if ($dayahisap) {
            return response()->json($dayahisap);
        }

        return response()->json(null, 404);
    }
}