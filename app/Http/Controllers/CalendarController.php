<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function index()
    {
        $roomTypes = DB::table('room_types')->orderBy('id')->get();
        return view('app', compact('roomTypes'));
    }

    public function availability(Request $r)
    {
        $from = Carbon::parse($r->query('from', today()->toDateString()))->startOfDay();
        $to = $from->copy()->addDays(14);

        $rows = DB::select(
            'SELECT rt.id AS room_type_id, rt.name, i.date, i.total, i.booked, i.closed,
                    (i.total - i.booked) AS remaining,
                    ' . PricingService::expr() . ' AS price
             FROM inventory i
             JOIN room_types rt ON rt.id = i.room_type_id
             WHERE i.date >= ? AND i.date < ?
             ORDER BY rt.id, i.date',
            [$from->toDateString(), $to->toDateString()]
        );

        return response()->json(['from' => $from->toDateString(), 'rows' => $rows]);
    }

    public function quote(Request $r)
    {
        $d = $r->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in'     => 'required|date',
            'check_out'    => 'required|date|after:check_in',
        ]);

        $rows = DB::select(
            'SELECT i.date, ' . PricingService::expr() . ' AS price
             FROM inventory i
             JOIN room_types rt ON rt.id = i.room_type_id
             WHERE i.room_type_id = ? AND i.date >= ? AND i.date < ?
             ORDER BY i.date',
            [$d['room_type_id'], $d['check_in'], $d['check_out']]
        );

        $total = array_sum(array_map(fn ($x) => (float) $x->price, $rows));

        return response()->json(['nights' => count($rows), 'total' => $total, 'rows' => $rows]);
    }

    public function summary()
    {
        $today = today()->toDateString();
        $end   = today()->addDays(30)->toDateString();

        $stats = [
            'bookings'   => (int) DB::selectOne("SELECT COUNT(*) AS v FROM bookings WHERE status = 'confirmed'")->v,
            'free_today' => (int) DB::selectOne('SELECT COALESCE(SUM(total - booked), 0) AS v FROM inventory WHERE date = ? AND closed = 0', [$today])->v,
            'occupancy'  => (float) DB::selectOne(
                'SELECT COALESCE(ROUND(100 * SUM(booked) / NULLIF(SUM(total), 0), 1), 0) AS v
                 FROM inventory WHERE date >= ? AND date < ?', [$today, $end])->v,
            'channels'   => (int) DB::selectOne('SELECT COUNT(*) AS v FROM channels WHERE is_active = 1')->v,
        ];

        $occupancy = DB::select(
            'SELECT rt.name, SUM(i.booked) AS booked, SUM(i.total) AS total,
                    ROUND(100 * SUM(i.booked) / SUM(i.total), 1) AS occupancy_pct
             FROM inventory i
             JOIN room_types rt ON rt.id = i.room_type_id
             WHERE i.date >= ? AND i.date < ?
             GROUP BY rt.id, rt.name
             ORDER BY rt.id',
            [$today, $end]
        );

        $channels = DB::select(
            "SELECT c.name, COUNT(b.id) AS bookings, COALESCE(SUM(b.units), 0) AS units
             FROM channels c
             LEFT JOIN bookings b ON b.channel_id = c.id AND b.status = 'confirmed'
             GROUP BY c.id, c.name
             ORDER BY bookings DESC"
        );

        $recent = DB::select(
            'SELECT b.id, b.guest_name, rt.name AS room, c.name AS channel,
                    b.check_in, b.check_out, b.units, b.status
             FROM bookings b
             JOIN room_types rt ON rt.id = b.room_type_id
             JOIN channels c ON c.id = b.channel_id
             ORDER BY b.id DESC
             LIMIT 8'
        );

        return response()->json(compact('stats', 'occupancy', 'channels', 'recent'));
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

    public function cancel(int $id, BookingService $svc)
    {
        try {
            $b = $svc->cancel($id);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['id' => $b->id, 'status' => $b->status]);
    }

    public function stopSales(Request $r)
    {
        $d = $r->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'from'         => 'required|date|after_or_equal:today',
            'to'           => 'required|date|after_or_equal:from',
            'closed'       => 'required|boolean',
        ]);

        $n = DB::update(
            'UPDATE inventory SET closed = ?
             WHERE room_type_id = ? AND date >= ? AND date <= ?',
            [$d['closed'] ? 1 : 0, $d['room_type_id'], $d['from'], $d['to']]
        );

        return response()->json(['nights' => $n]);
    }
}