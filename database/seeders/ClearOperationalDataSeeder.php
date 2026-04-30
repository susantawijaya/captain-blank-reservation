<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClearOperationalDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            Complaint::query()->delete();
            Review::query()->delete();
            Payment::query()->delete();
            Reservation::query()->delete();

            User::query()
                ->where('is_master_admin', false)
                ->delete();

            DB::table('password_reset_tokens')->delete();

            Schedule::query()->update([
                'booked_count' => 0,
            ]);

            Schedule::query()
                ->whereIn('status', ['tersedia', 'penuh'])
                ->update([
                    'status' => 'tersedia',
                ]);
        });
    }
}
