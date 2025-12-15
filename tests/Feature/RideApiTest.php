<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RideApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_create_ride_success_positive_case(): void
{
    // Persiapan data user dummy (jika pakai factory)
    // $user = User::factory()->create(); 
    
    // Skenario Positif
    $response = $this->postJson('/api/rides', [
        'user_id' => 1, // Pastikan user ID 1 ada di DB atau gunakan factory
        'pickup_location' => 'Del Institute',
        'dropoff_location' => 'Balige Market',
        'price' => 15000
    ]);

    $response->assertStatus(201) // Ekspektasi HTTP 201 Created
             ->assertJsonPath('status', 'success');
}

    public function test_create_ride_validation_error_negative_case(): void
    {
        // Skenario Negatif: Harga kosong
        $response = $this->postJson('/api/rides', [
            'pickup_location' => 'Del Institute',
            // 'price' => dikosongkan
        ]);

        $response->assertStatus(400) // Ekspektasi HTTP 400 Bad Request
                ->assertJsonStructure(['errors' => ['price']]);
    }
}
