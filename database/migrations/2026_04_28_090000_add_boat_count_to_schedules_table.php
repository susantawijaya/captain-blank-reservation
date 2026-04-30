<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedInteger('boat_count')->default(1)->after('capacity');
        });

        $schedules = DB::table('schedules')->select('id', 'booked_count', 'status')->get();

        foreach ($schedules as $schedule) {
            $activeReservationCount = DB::table('reservations')
                ->where('schedule_id', $schedule->id)
                ->where('status', '!=', 'dibatalkan')
                ->count();

            $legacyBookedCount = (int) $schedule->booked_count;
            $normalizedBookedCount = max($activeReservationCount, $legacyBookedCount > 0 ? 1 : 0);
            $boatCount = max(3, $normalizedBookedCount > 0 ? $normalizedBookedCount + 2 : 3);
            $status = in_array($schedule->status, ['tersedia', 'penuh'], true)
                ? ($normalizedBookedCount >= $boatCount ? 'penuh' : 'tersedia')
                : $schedule->status;

            DB::table('schedules')
                ->where('id', $schedule->id)
                ->update([
                    'boat_count' => $boatCount,
                    'booked_count' => min($normalizedBookedCount, $boatCount),
                    'status' => $status,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('boat_count');
        });
    }
};
