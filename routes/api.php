<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/events', [EventController::class, 'store'])->middleware('role:organizer,admin');
    Route::put('/events/{id}', [EventController::class, 'update'])->middleware('role:organizer,admin');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->middleware('role:organizer,admin');

    Route::post('/events/{event_id}/tickets', [TicketController::class, 'store'])->middleware('role:organizer,admin');
    Route::put('/tickets/{id}', [TicketController::class, 'update'])->middleware('role:organizer,admin');
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy'])->middleware('role:organizer,admin');

    Route::post('/tickets/{ticket_id}/bookings', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    Route::post('/bookings/{booking_id}/payment', [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
});
