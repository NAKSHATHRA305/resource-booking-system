<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalendarController::class, 'index']);
Route::get('/calendar', [CalendarController::class, 'index']);
Route::get('/availability', [CalendarController::class, 'availability']);
Route::get('/quote', [CalendarController::class, 'quote']);
Route::get('/summary', [CalendarController::class, 'summary']);

Route::get('/login', [AuthController::class, 'show']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware(AdminOnly::class)->group(function () {
    Route::post('/bookings', [CalendarController::class, 'store']);
    Route::post('/bookings/{id}/cancel', [CalendarController::class, 'cancel'])->whereNumber('id');
    Route::post('/stop-sales', [CalendarController::class, 'stopSales']);
});