<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function book(array $d): Booking
    {
        $in    = Carbon::parse($d['check_in'])->startOfDay();
        $out   = Carbon::parse($d['check_out'])->startOfDay();
        $units = $d['units'] ?? 1;
        $nights = (int) $in->diffInDays($out);

        if ($nights < 1) {
            throw new \DomainException('Check-out must be after check-in.');
        }

        return DB::transaction(function () use ($d, $in, $out, $units, $nights) {
            // 1. Lock every inventory row for these nights, in date order
            $rows = DB::select(
                'SELECT id, date, total, booked FROM inventory
                 WHERE room_type_id = ? AND date >= ? AND date < ?
                 ORDER BY date FOR UPDATE',
                [$d['room_type_id'], $in->toDateString(), $out->toDateString()]
            );

            // 2. Idempotency: a repeat message from a channel returns the original booking
            if (!empty($d['external_ref'])) {
                $existing = Booking::where('channel_id', $d['channel_id'])
                    ->where('external_ref', $d['external_ref'])
                    ->lockForUpdate()
                    ->first();
                if ($existing) {
                    return $existing;
                }
            }

            // 3. Validate
            if (count($rows) !== $nights) {
                throw new \DomainException('Inventory is not set for some dates.');
            }
            foreach ($rows as $r) {
                if ($r->booked + $units > $r->total) {
                    throw new \DomainException("Sold out on {$r->date}.");
                }
            }

            // 4. Take the inventory
            DB::update(
                'UPDATE inventory SET booked = booked + ?
                 WHERE room_type_id = ? AND date >= ? AND date < ?',
                [$units, $d['room_type_id'], $in->toDateString(), $out->toDateString()]
            );

            // 5. Create the booking and log it
            $booking = Booking::create([
                'channel_id'   => $d['channel_id'],
                'room_type_id' => $d['room_type_id'],
                'user_id'      => $d['user_id'] ?? null,
                'guest_name'   => $d['guest_name'],
                'check_in'     => $in,
                'check_out'    => $out,
                'units'        => $units,
                'status'       => 'confirmed',
                'external_ref' => $d['external_ref'] ?? null,
            ]);

            DB::table('booking_logs')->insert([
                'booking_id' => $booking->id,
                'old_status' => null,
                'new_status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $booking;
        });
    }
}