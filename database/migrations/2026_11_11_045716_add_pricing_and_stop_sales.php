<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->unsignedInteger('base_rate')->default(10000)->after('default_units');
        });
        Schema::table('inventory', function (Blueprint $table) {
            $table->boolean('closed')->default(false)->after('booked');
        });

        DB::table('room_types')->where('name', 'Single Room')->update(['base_rate' => 8000]);
        DB::table('room_types')->where('name', 'Double Room')->update(['base_rate' => 12000]);
        DB::table('room_types')->where('name', 'Suite')->update(['base_rate' => 25000]);
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn('closed');
        });
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('base_rate');
        });
    }
};