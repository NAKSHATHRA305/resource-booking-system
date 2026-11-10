<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChannelBookingController extends Controller
{
    public function store(Request $r, BookingService $svc)
    {
        $key = $r->header('X-Api-Key');
        $channel = $key
            ? DB::table('channels')->where('api_key', $key)->where('is_active', true)->first()
            : null;

        if (!$channel) {
            return response()->json(['message' => 'Invalid API key'], 401);
        }

        $data = $r->validate([
            'external_ref' => 'required|string|max:100',
            'room_type_id' => 'required|exists:room_types,id',
            'guest_name'   => 'required|string|max:100',
            'check_in'     => 'required|date|after_or_equal:today',
            'check_out'    => 'required|date|after:check_in',
            'units'        => 'nullable|integer|min:1|max:10',
        ]);
        $data['units'] = $data['units'] ?? 1;

        try {
            $booking = $svc->book($data + ['channel_id' => $channel->id]);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(
            ['id' => $booking->id, 'duplicate' => !$booking->wasRecentlyCreated],
            $booking->wasRecentlyCreated ? 201 : 200
        );
    }
}