<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('channels')->insert([
            ['name' => 'direct',  'api_key' => null,           'created_at' => now(), 'updated_at' => now()],
            ['name' => 'sim-ota', 'api_key' => 'test-key-123', 'created_at' => now(), 'updated_at' => now()],
        ]);

        foreach ([['Single Room', 5], ['Double Room', 8], ['Suite', 2]] as [$name, $units]) {
            $id = DB::table('room_types')->insertGetId([
                'name'          => $name,
                'default_units' => $units,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            for ($i = 0; $i < 60; $i++) {
                DB::table('inventory')->insert([
                    'room_type_id' => $id,
                    'date'         => Carbon::today()->addDays($i)->toDateString(),
                    'total'        => $units,
                    'booked'       => 0,
                ]);
            }
        }
    }
}