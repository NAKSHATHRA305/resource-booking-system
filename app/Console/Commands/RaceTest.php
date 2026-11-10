<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RaceTest extends Command
{
    protected $signature = 'race:test {mode=safe} {--workers=10} {--attempt} {--at=0} {--worker=0}';
    protected $description = 'Fire concurrent bookings at 1 remaining unit (mode: safe|unsafe)';

    const ROOM = 3;

    public function handle(): int
    {
        $date = Carbon::today()->addDays(30)->toDateString();
        return $this->option('attempt') ? $this->attempt($date) : $this->runTest($date);
    }

    private function runTest(string $date): int
    {
        $mode = $this->argument('mode');
        $n = (int) $this->option('workers');

        DB::table('bookings')->where('room_type_id', self::ROOM)->where('check_in', $date)->delete();
        DB::table('inventory')->where('room_type_id', self::ROOM)->where('date', $date)
            ->update(['total' => 1, 'booked' => 0]);

        $at = microtime(true) + 4;   // all workers wait until this moment, then fire together
        $procs = [];
        for ($i = 0; $i < $n; $i++) {
            $cmd = [PHP_BINARY, base_path('artisan'), 'race:test', $mode, '--attempt', '--at=' . $at, '--worker=' . $i];
            $procs[] = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
            $pipesAll[] = $pipes;
        }

        $ok = 0;
        foreach ($procs as $i => $p) {
            $out = stream_get_contents($pipesAll[$i][1]);
            proc_close($p);
            if (str_contains($out, 'BOOKED')) $ok++;
        }

        $bookings = DB::table('bookings')->where('room_type_id', self::ROOM)->where('check_in', $date)->count();
        $inv = DB::table('inventory')->where('room_type_id', self::ROOM)->where('date', $date)->first();

        $this->info("Mode: $mode | workers: $n | units available: 1");
        $this->info("Workers that got a booking: $ok");
        $this->info("Bookings in DB: $bookings | inventory booked {$inv->booked} of total {$inv->total}");
        $this->line($bookings > $inv->total ? 'RESULT: OVERBOOKED' : 'RESULT: OK');
        return 0;
    }

    private function attempt(string $date): int
    {
        $at = (float) $this->option('at');
        while (microtime(true) < $at) usleep(1000);
        $w = (int) $this->option('worker');

        try {
            if ($this->argument('mode') === 'unsafe') {
                $this->unsafeBook($date, $w);
            } else {
                app(BookingService::class)->book([
                    'channel_id'   => 1,
                    'room_type_id' => self::ROOM,
                    'guest_name'   => "race-$w",
                    'check_in'     => $date,
                    'check_out'    => Carbon::parse($date)->addDay()->toDateString(),
                    'units'        => 1,
                ]);
            }
            $this->line('BOOKED');
        } catch (\DomainException $e) {
            $this->line('REJECTED');
        }
        return 0;
    }

    // Same logic as BookingService but WITHOUT the row lock, to reproduce the bug
    private function unsafeBook(string $date, int $w): void
    {
        DB::transaction(function () use ($date, $w) {
            $row = DB::selectOne(
                'SELECT total, booked FROM inventory WHERE room_type_id = ? AND date = ?',
                [self::ROOM, $date]
            );
            if ($row->booked + 1 > $row->total) throw new \DomainException('Sold out');

            usleep(300000);   // simulates the gap between check and write

            DB::update('UPDATE inventory SET booked = booked + 1 WHERE room_type_id = ? AND date = ?', [self::ROOM, $date]);
            DB::table('bookings')->insert([
                'channel_id' => 1, 'room_type_id' => self::ROOM, 'guest_name' => "race-$w",
                'check_in' => $date, 'check_out' => Carbon::parse($date)->addDay()->toDateString(),
                'units' => 1, 'status' => 'confirmed', 'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }
}