<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('snorkeling_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('booking_date');
            $table->unsignedInteger('participants')->default(1);
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('pickup_location')->nullable();
            $table->unsignedInteger('total_price');
            $table->enum('status', [
                'menunggu_pembayaran',
                'menunggu_verifikasi',
                'terkonfirmasi',
                'selesai',
                'dibatalkan',
                'dijadwalkan_ulang',
            ])->default('menunggu_pembayaran');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
