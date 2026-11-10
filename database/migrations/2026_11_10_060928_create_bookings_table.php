<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained();
            $table->foreignId('room_type_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('guest_name');
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedInteger('units')->default(1);
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('confirmed');
            $table->string('external_ref')->nullable();
            $table->timestamps();
            $table->unique(['channel_id', 'external_ref']);
            $table->index(['room_type_id', 'check_in', 'check_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};