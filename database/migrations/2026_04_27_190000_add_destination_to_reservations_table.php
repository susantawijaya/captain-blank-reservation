<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('destination_id')->nullable()->after('snorkeling_package_id')->constrained()->nullOnDelete();
        });

        DB::table('reservations')
            ->select(['id', 'snorkeling_package_id'])
            ->orderBy('id')
            ->get()
            ->each(function ($reservation) {
                $destinationId = DB::table('destination_snorkeling_package')
                    ->where('snorkeling_package_id', $reservation->snorkeling_package_id)
                    ->orderBy('id')
                    ->value('destination_id');

                if ($destinationId) {
                    DB::table('reservations')
                        ->where('id', $reservation->id)
                        ->update(['destination_id' => $destinationId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('destination_id');
        });
    }
};
