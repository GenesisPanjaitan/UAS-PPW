<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ride;

class RideApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $driver;

    /**
     * Setup method untuk membuat data yang diperlukan di setiap test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Buat user dummy untuk testing
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@test.com'
        ]);

        $this->driver = User::factory()->create([
            'name' => 'Test Driver',
            'email' => 'driver@test.com'
        ]);
    }

    /* ========================================
       TEST CASES: CREATE RIDE (POST /api/rides)
       ======================================== */

    /**
     * [POSITIVE] Test membuat ride dengan data yang valid
     */
    public function test_create_ride_with_valid_data_returns_201(): void
    {
        $response = $this->postJson('/api/rides', [
            'user_id' => $this->user->id,
            'pickup_location' => 'Del Institute of Technology',
            'dropoff_location' => 'Balige Market',
            'price' => 15000
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'id',
                         'user_id',
                         'pickup_location',
                         'dropoff_location',
                         'price',
                         'status',
                         'created_at',
                         'updated_at'
                     ]
                 ])
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.status', 'pending');

        // Pastikan data tersimpan di database
        $this->assertDatabaseHas('rides', [
            'user_id' => $this->user->id,
            'pickup_location' => 'Del Institute of Technology',
            'dropoff_location' => 'Balige Market',
            'price' => 15000,
            'status' => 'pending'
        ]);
    }

    /**
     * [NEGATIVE] Test membuat ride tanpa user_id (validasi required)
     */
    public function test_create_ride_without_user_id_returns_400(): void
    {
        $response = $this->postJson('/api/rides', [
            'pickup_location' => 'Del Institute',
            'dropoff_location' => 'Balige Market',
            'price' => 15000
        ]);

        $response->assertStatus(400)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'errors' => ['user_id']
                 ])
                 ->assertJsonPath('status', 'error');
    }

    /**
     * [NEGATIVE] Test membuat ride dengan user_id yang tidak ada
     */
    public function test_create_ride_with_invalid_user_id_returns_400(): void
    {
        $response = $this->postJson('/api/rides', [
            'user_id' => 99999, // ID yang tidak ada
            'pickup_location' => 'Del Institute',
            'dropoff_location' => 'Balige Market',
            'price' => 15000
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonStructure(['errors' => ['user_id']]);
    }

    /**
     * [NEGATIVE] Test membuat ride tanpa price
     */
    public function test_create_ride_without_price_returns_400(): void
    {
        $response = $this->postJson('/api/rides', [
            'user_id' => $this->user->id,
            'pickup_location' => 'Del Institute',
            'dropoff_location' => 'Balige Market'
        ]);

        $response->assertStatus(400)
                 ->assertJsonStructure(['errors' => ['price']]);
    }

    /**
     * [NEGATIVE] Test membuat ride dengan price negatif
     */
    public function test_create_ride_with_negative_price_returns_400(): void
    {
        $response = $this->postJson('/api/rides', [
            'user_id' => $this->user->id,
            'pickup_location' => 'Del Institute',
            'dropoff_location' => 'Balige Market',
            'price' => -5000
        ]);

        $response->assertStatus(400)
                 ->assertJsonStructure(['errors' => ['price']]);
    }

    /**
     * [NEGATIVE] Test membuat ride dengan price kurang dari minimum (< 1000)
     */
    public function test_create_ride_with_price_below_minimum_returns_400(): void
    {
        $response = $this->postJson('/api/rides', [
            'user_id' => $this->user->id,
            'pickup_location' => 'Del Institute',
            'dropoff_location' => 'Balige Market',
            'price' => 500
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('status', 'error');
    }

    /**
     * [NEGATIVE] Test membuat ride tanpa pickup_location
     */
    public function test_create_ride_without_pickup_location_returns_400(): void
    {
        $response = $this->postJson('/api/rides', [
            'user_id' => $this->user->id,
            'dropoff_location' => 'Balige Market',
            'price' => 15000
        ]);

        $response->assertStatus(400)
                 ->assertJsonStructure(['errors' => ['pickup_location']]);
    }

    /* ========================================
       TEST CASES: GET RIDE DETAIL (GET /api/rides/{id})
       ======================================== */

    /**
     * [POSITIVE] Test mengambil detail ride yang ada
     */
    public function test_get_existing_ride_returns_200(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'pickup_location' => 'Point A',
            'dropoff_location' => 'Point B',
            'price' => 20000
        ]);

        $response = $this->getJson("/api/rides/{$ride->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.id', $ride->id)
                 ->assertJsonPath('data.price', '20000.00');
    }

    /**
     * [NEGATIVE] Test mengambil ride yang tidak ada
     */
    public function test_get_nonexistent_ride_returns_404(): void
    {
        $response = $this->getJson('/api/rides/99999');

        $response->assertStatus(404)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'Order tidak ditemukan');
    }

    /* ========================================
       TEST CASES: GET ALL RIDES (GET /api/rides)
       ======================================== */

    /**
     * [POSITIVE] Test mendapatkan list semua rides
     */
    public function test_get_all_rides_returns_200(): void
    {
        Ride::factory()->count(5)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson('/api/rides');

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'data',
                         'current_page',
                         'per_page',
                         'total'
                     ]
                 ]);
    }

    /**
     * [POSITIVE] Test filter rides berdasarkan status
     */
    public function test_get_rides_filtered_by_status_returns_200(): void
    {
        Ride::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);
        Ride::factory()->create(['user_id' => $this->user->id, 'status' => 'completed']);

        $response = $this->getJson('/api/rides?status=pending');

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');
    }

    /* ========================================
       TEST CASES: UPDATE RIDE (PUT /api/rides/{id})
       ======================================== */

    /**
     * [POSITIVE] Test update ride dengan status pending
     */
    public function test_update_pending_ride_returns_200(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'price' => 15000
        ]);

        $response = $this->putJson("/api/rides/{$ride->id}", [
            'price' => 20000,
            'pickup_location' => 'New Pickup Location'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.price', '20000.00');
    }

    /**
     * [NEGATIVE] Test update ride yang sudah accepted (tidak boleh)
     */
    public function test_update_accepted_ride_returns_409(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'accepted',
            'driver_id' => $this->driver->id
        ]);

        $response = $this->putJson("/api/rides/{$ride->id}", [
            'price' => 25000
        ]);

        $response->assertStatus(409)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'Tidak bisa mengubah order yang sudah diproses');
    }

    /* ========================================
       TEST CASES: DELETE RIDE (DELETE /api/rides/{id})
       ======================================== */

    /**
     * [POSITIVE] Test delete ride dengan status pending
     */
    public function test_delete_pending_ride_returns_200(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        $response = $this->deleteJson("/api/rides/{$ride->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        // Pastikan data terhapus dari database
        $this->assertDatabaseMissing('rides', ['id' => $ride->id]);
    }

    /**
     * [NEGATIVE] Test delete ride yang sudah completed (tidak boleh)
     */
    public function test_delete_completed_ride_returns_409(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);

        $response = $this->deleteJson("/api/rides/{$ride->id}");

        $response->assertStatus(409)
                 ->assertJsonPath('status', 'error');

        // Pastikan data masih ada
        $this->assertDatabaseHas('rides', ['id' => $ride->id]);
    }

    /**
     * [NEGATIVE] Test delete ride yang tidak ada
     */
    public function test_delete_nonexistent_ride_returns_404(): void
    {
        $response = $this->deleteJson('/api/rides/99999');

        $response->assertStatus(404)
                 ->assertJsonPath('status', 'error');
    }

    /* ========================================
       TEST CASES: ACCEPT RIDE (PUT /api/rides/{id}/accept)
       ======================================== */

    /**
     * [POSITIVE] Test driver accept ride dengan status pending
     */
    public function test_accept_pending_ride_returns_200(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        $response = $this->putJson("/api/rides/{$ride->id}/accept", [
            'driver_id' => $this->driver->id
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.status', 'accepted')
                 ->assertJsonPath('data.driver_id', $this->driver->id);

        // Verifikasi di database
        $this->assertDatabaseHas('rides', [
            'id' => $ride->id,
            'status' => 'accepted',
            'driver_id' => $this->driver->id
        ]);
    }

    /**
     * [NEGATIVE] Test accept ride tanpa driver_id
     */
    public function test_accept_ride_without_driver_id_returns_400(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        $response = $this->putJson("/api/rides/{$ride->id}/accept", []);

        $response->assertStatus(400)
                 ->assertJsonStructure(['errors' => ['driver_id']]);
    }

    /**
     * [NEGATIVE] Test accept ride yang sudah accepted (conflict)
     */
    public function test_accept_already_accepted_ride_returns_409(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'accepted',
            'driver_id' => $this->driver->id
        ]);

        $anotherDriver = User::factory()->create();

        $response = $this->putJson("/api/rides/{$ride->id}/accept", [
            'driver_id' => $anotherDriver->id
        ]);

        $response->assertStatus(409)
                 ->assertJsonPath('message', 'Order sudah diambil atau dibatalkan');
    }

    /* ========================================
       TEST CASES: COMPLETE RIDE (PUT /api/rides/{id}/complete)
       ======================================== */

    /**
     * [POSITIVE] Test complete ride yang sedang accepted
     */
    public function test_complete_accepted_ride_returns_200(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'driver_id' => $this->driver->id,
            'status' => 'accepted'
        ]);

        $response = $this->putJson("/api/rides/{$ride->id}/complete");

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('rides', [
            'id' => $ride->id,
            'status' => 'completed'
        ]);
    }

    /**
     * [NEGATIVE] Test complete ride yang statusnya pending (belum accepted)
     */
    public function test_complete_pending_ride_returns_409(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        $response = $this->putJson("/api/rides/{$ride->id}/complete");

        $response->assertStatus(409)
                 ->assertJsonPath('message', 'Order harus dalam status accepted untuk diselesaikan');
    }

    /**
     * [NEGATIVE] Test complete ride yang tidak ada
     */
    public function test_complete_nonexistent_ride_returns_404(): void
    {
        $response = $this->putJson('/api/rides/99999/complete');

        $response->assertStatus(404)
                 ->assertJsonPath('status', 'error');
    }

    /* ========================================
       TEST CASES: CANCEL RIDE (PUT /api/rides/{id}/cancel)
       ======================================== */

    /**
     * [POSITIVE] Test cancel ride dengan status pending
     */
    public function test_cancel_pending_ride_returns_200(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        $response = $this->putJson("/api/rides/{$ride->id}/cancel");

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.status', 'canceled');
    }

    /**
     * [POSITIVE] Test cancel ride dengan status accepted
     */
    public function test_cancel_accepted_ride_returns_200(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'accepted',
            'driver_id' => $this->driver->id
        ]);

        $response = $this->putJson("/api/rides/{$ride->id}/cancel");

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'canceled');
    }

    /**
     * [NEGATIVE] Test cancel ride yang sudah completed
     */
    public function test_cancel_completed_ride_returns_409(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);

        $response = $this->putJson("/api/rides/{$ride->id}/cancel");

        $response->assertStatus(409)
                 ->assertJsonPath('message', 'Tidak bisa membatalkan order yang sudah selesai');
    }

    /* ========================================
       TEST CASES: DATA CONSISTENCY
       ======================================== */

    /**
     * [CONSISTENCY] Test bahwa data price disimpan dengan benar (2 desimal)
     */
    public function test_ride_price_stored_with_correct_decimal_format(): void
    {
        $response = $this->postJson('/api/rides', [
            'user_id' => $this->user->id,
            'pickup_location' => 'Location A',
            'dropoff_location' => 'Location B',
            'price' => 15000.50
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.price', '15000.50');
    }

    /**
     * [CONSISTENCY] Test relationship antara Ride dan User
     */
    public function test_ride_has_correct_user_relationship(): void
    {
        $ride = Ride::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson("/api/rides/{$ride->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.user.email', $this->user->email);
    }
}
