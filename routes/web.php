<?php

use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalendarController::class, 'index']);
Route::get('/calendar', [CalendarController::class, 'index']);
Route::get('/availability', [CalendarController::class, 'availability']);
Route::get('/summary', [CalendarController::class, 'summary']);
Route::post('/bookings', [CalendarController::class, 'store']);