<?php

use App\Http\Controllers\ChannelBookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/channel/bookings', [ChannelBookingController::class, 'store']);