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
            $table->unsignedInteger('adult_count')->default(1)->after('participants');
            $table->unsignedInteger('child_count')->default(0)->after('adult_count');
        });

        DB::table('reservations')->update([
            'adult_count' => DB::raw('participants'),
            'child_count' => 0,
        ]);
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['adult_count', 'child_count']);
        });
    }
};
