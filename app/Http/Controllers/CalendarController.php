<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function index()
    {
        $roomTypes = DB::table('room_types')->orderBy('id')->get();
        return view('calendar', compact('roomTypes'));
    }

    public function availability(Request $r)
    {
        $from = Carbon::parse($r->query('from', today()->toDateString()))->startOfDay();
        $to = $from->copy()->addDays(14);

        $rows = DB::select(
            'SELECT rt.id AS room_type_id, rt.name, i.date, i.total, i.booked,
                    (i.total - i.booked) AS remaining
             FROM inventory i
             JOIN room_types rt ON rt.id = i.room_type_id
             WHERE i.date >= ? AND i.date < ?
             ORDER BY rt.id, i.date',
            [$from->toDateString(), $to->toDateString()]
        );

        return response()->json(['from' => $from->toDateString(), 'rows' => $rows]);
    }

    public function store(Request $r, BookingService $svc)
    {
        $data = $r->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'guest_name'   => 'required|string|max:100',
            'check_in'     => 'required|date|after_or_equal:today',
            'check_out'    => 'required|date|after:check_in',
            'units'        => 'nullable|integer|min:1|max:10',
        ]);
        $data['units'] = $data['units'] ?? 1;

        try {
            $booking = $svc->book($data + ['channel_id' => 1]);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['id' => $booking->id]);
    }
}