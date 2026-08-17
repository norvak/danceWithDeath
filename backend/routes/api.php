<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AvailabilityController;
use Illuminate\Support\Facades\Route;


Route::get('/availability', AvailabilityController::class);
Route::post('/appointments', [AppointmentController::class, 'store']);
