<?php

use App\Http\Controllers\Api\RideController;
use Illuminate\Support\Facades\Route;

Route::post('/rides', [RideController::class, 'store']); // Create Order
Route::get('/rides/{id}', [RideController::class, 'show']); // Read Detail
Route::put('/rides/{id}/accept', [RideController::class, 'accept']); // Update (Accept)
Route::put('/rides/{id}/complete', [RideController::class, 'complete']); // Update (Complete)