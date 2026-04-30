<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snorkeling_package_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->unsignedInteger('capacity')->default(8);
            $table->unsignedInteger('booked_count')->default(0);
            $table->enum('status', ['tersedia', 'penuh', 'selesai', 'batal_cuaca', 'reschedule'])->default('tersedia');
            $table->text('weather_note')->nullable();
            $table->text('destination_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
