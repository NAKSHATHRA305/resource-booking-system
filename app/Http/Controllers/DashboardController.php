<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $byChannel = DB::select(
            "SELECT c.name, COUNT(b.id) AS bookings, COALESCE(SUM(b.units), 0) AS units
             FROM channels c
             LEFT JOIN bookings b ON b.channel_id = c.id AND b.status = 'confirmed'
             GROUP BY c.id, c.name
             ORDER BY bookings DESC"
        );

        $occupancy = DB::select(
            "SELECT rt.name, SUM(i.booked) AS booked, SUM(i.total) AS total,
                    ROUND(100 * SUM(i.booked) / SUM(i.total), 1) AS occupancy_pct
             FROM inventory i
             JOIN room_types rt ON rt.id = i.room_type_id
             WHERE i.date >= CURDATE() AND i.date < CURDATE() + INTERVAL 30 DAY
             GROUP BY rt.id, rt.name
             ORDER BY occupancy_pct DESC"
        );

        $byStatus = DB::select(
            "SELECT status, COUNT(*) AS total FROM bookings GROUP BY status"
        );

        $busiest = DB::select(
            "SELECT date, SUM(booked) AS booked, SUM(total) AS total
             FROM inventory
             WHERE date >= CURDATE()
             GROUP BY date
             HAVING SUM(booked) > 0
             ORDER BY SUM(booked) DESC, date
             LIMIT 5"
        );

        return view('dashboard', compact('byChannel', 'occupancy', 'byStatus', 'busiest'));
    }
}