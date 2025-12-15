<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RideController extends Controller
{
    // 1. CREATE: Order Ojek (POST)
    public function store(Request $request)
    {
        // Validasi Input
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id', // Simulasi auth, idealnya pakai Auth::id()
            'pickup_location' => 'required|string',
            'dropoff_location' => 'required|string',
            'price' => 'required|numeric|min:1000',
        ]);

        // Error Handling 400 Bad Request
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Simpan Data
        $ride = Ride::create($request->all());

        // Response 201 Created
        return response()->json([
            'status' => 'success',
            'message' => 'Order berhasil dibuat',
            'data' => $ride
        ], 201);
    }

    // 2. READ: Lihat Detail Order (GET)
    public function show($id)
    {
        $ride = Ride::find($id);

        if (!$ride) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        return response()->json(['data' => $ride], 200);
    }

    // 3. UPDATE: Driver Terima Order (PUT)
    public function accept($id, Request $request)
    {
        $ride = Ride::find($id);

        if (!$ride) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        // Validasi Logika: Tidak bisa ambil order yang sudah diambil orang
        if ($ride->status !== 'pending') {
            return response()->json(['message' => 'Order sudah diambil atau dibatalkan'], 409); // 409 Conflict
        }

        // Update status & set driver
        $ride->update([
            'status' => 'accepted',
            'driver_id' => $request->driver_id // Simulasi driver ID
        ]);

        return response()->json(['message' => 'Order diterima', 'data' => $ride], 200);
    }
    
    // 4. UPDATE: Selesaikan Order (PUT)
    public function complete($id)
    {
        $ride = Ride::find($id);
        
        if (!$ride) return response()->json(['message' => 'Not Found'], 404);

        $ride->update(['status' => 'completed']);

        return response()->json(['message' => 'Perjalanan selesai', 'data' => $ride], 200);
    }
}