<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/calendar', [CalendarController::class, 'index']);
Route::get('/availability', [CalendarController::class, 'availability']);
Route::post('/bookings', [CalendarController::class, 'store']);

Route::get('/dashboard', [DashboardController::class, 'index']);