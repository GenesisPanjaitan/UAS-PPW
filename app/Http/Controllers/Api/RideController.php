<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RideController extends Controller
{

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $status = $request->get('status'); 

            $query = Ride::with(['user', 'driver']);
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $rides = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data rides berhasil diambil',
                'data' => $rides
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

  
    public function store(Request $request)
    {
        // Validasi Input
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'pickup_location' => 'required|string|min:3|max:255',
            'dropoff_location' => 'required|string|min:3|max:255',
            'price' => 'required|numeric|min:1000|max:1000000',
        ], [
            'user_id.required' => 'User ID wajib diisi',
            'user_id.exists' => 'User tidak ditemukan',
            'pickup_location.required' => 'Lokasi jemput wajib diisi',
            'dropoff_location.required' => 'Lokasi tujuan wajib diisi',
            'price.required' => 'Harga wajib diisi',
            'price.min' => 'Harga minimal Rp 1.000',
            'price.max' => 'Harga maksimal Rp 1.000.000',
        ]);

        // Error Handling 400 Bad Request
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Simpan Data
            $ride = Ride::create([
                'user_id' => $request->user_id,
                'pickup_location' => $request->pickup_location,
                'dropoff_location' => $request->dropoff_location,
                'price' => $request->price,
                'status' => 'pending' // Default status
            ]);

            // Response 201 Created
            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil dibuat',
                'data' => $ride
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

  
    public function show($id)
    {
        try {
            $ride = Ride::with(['user', 'driver'])->find($id);

            if (!$ride) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $ride
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

 
    public function update(Request $request, $id)
    {
        try {
            $ride = Ride::find($id);

            if (!$ride) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            // Validasi: hanya bisa update jika status pending
            if ($ride->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak bisa mengubah order yang sudah diproses'
                ], 409); // 409 Conflict
            }

            // Validasi Input
            $validator = Validator::make($request->all(), [
                'pickup_location' => 'sometimes|string|min:3|max:255',
                'dropoff_location' => 'sometimes|string|min:3|max:255',
                'price' => 'sometimes|numeric|min:1000|max:1000000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 400);
            }

            $ride->update($request->only(['pickup_location', 'dropoff_location', 'price']));

            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil diupdate',
                'data' => $ride
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $ride = Ride::find($id);

            if (!$ride) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            // Validasi: tidak bisa delete ride yang sedang berjalan
            if (in_array($ride->status, ['accepted', 'completed'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak bisa menghapus order yang sudah diproses atau selesai'
                ], 409);
            }

            $ride->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil dihapus'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function accept($id, Request $request)
    {
        try {
            $ride = Ride::find($id);

            if (!$ride) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            // Validasi Logika: Tidak bisa ambil order yang sudah diambil orang
            if ($ride->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order sudah diambil atau dibatalkan'
                ], 409); // 409 Conflict
            }

            // Validasi driver_id
            $validator = Validator::make($request->all(), [
                'driver_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Update status & set driver
            $ride->update([
                'status' => 'accepted',
                'driver_id' => $request->driver_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil diterima',
                'data' => $ride->load('driver')
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menerima order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
  
    public function complete($id)
    {
        try {
            $ride = Ride::find($id);
            
            if (!$ride) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            // Validasi: hanya bisa complete jika statusnya accepted
            if ($ride->status !== 'accepted') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order harus dalam status accepted untuk diselesaikan'
                ], 409);
            }

            $ride->update(['status' => 'completed']);

            return response()->json([
                'status' => 'success',
                'message' => 'Perjalanan selesai',
                'data' => $ride
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyelesaikan perjalanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function cancel($id)
    {
        try {
            $ride = Ride::find($id);
            
            if (!$ride) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            // Validasi: tidak bisa cancel jika sudah completed
            if ($ride->status === 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak bisa membatalkan order yang sudah selesai'
                ], 409);
            }

            $ride->update(['status' => 'canceled']);

            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil dibatalkan',
                'data' => $ride
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
