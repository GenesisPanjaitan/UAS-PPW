<?php

use App\Http\Controllers\Api\RideController;
use Illuminate\Support\Facades\Route;

// CRUD Endpoints untuk Ride
Route::get('/rides', [RideController::class, 'index']); // List All Rides
Route::post('/rides', [RideController::class, 'store']); // Create Order
Route::get('/rides/{id}', [RideController::class, 'show']); // Read Detail
Route::put('/rides/{id}', [RideController::class, 'update']); // Update Ride
Route::delete('/rides/{id}', [RideController::class, 'destroy']); // Delete Ride

// Action Endpoints
Route::put('/rides/{id}/accept', [RideController::class, 'accept']); // Accept Order
Route::put('/rides/{id}/complete', [RideController::class, 'complete']); // Complete Ride
Route::put('/rides/{id}/cancel', [RideController::class, 'cancel']); // Cancel Ride
